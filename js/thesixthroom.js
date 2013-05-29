/*******************************************************
  MAIN FUNCTION 
*******************************************************/
var interval=self.setInterval(function(){checkForNewPeople()},5000);

function checkForNewPeople()
{
  $.ajax({
             url: 'http://thesixthroom.org/includes/check_for_new_visitors.php' , 
             type: 'POST',
             data: "&after_date=" + lastTime,
             dataType: "json",
             success: function(result){  
                  if (result["text"] && result["text"].length > 0){
                      $('#person-entered').show();
                      $('#person-entered p').html(result["text"]);
                      $('#person-entered p').one().animate({top:'-1px'}, 500).delay(5000).animate({top:'-100px'}, 500, function(){$('#person-entered').hide();});    
                      lastTime = result["new_time"];
                      window.setTimeout(function(){
                        drawForcedGraph('data/networkdata_' + model + '_' + window.currentNetworkDate + '.json', true);
                      },2000);
                      //basically force a refresh to incorporate the new node
                      
                  }
              
             }
          });   
  
}

if (model == "space"){
  $("#time-button").removeClass("selected");
  $("#space-button").addClass("selected");
}
else{
  $("#time-button").addClass("selected");
  $("#space-button").removeClass("selected");
}
window.continentsToColors = { "Antarctica":"#ff7f0d", 
                              "Australia":"#1e77b4", 
                              "Asia":"#ffbb78", 
                              "Africa":"#afc6e8", 
                              "South America":"#d62628", 
                              "Europe":"#97df8a", 
                              "North America":"#2ba02b"};
window.venuesToColors =     {'guestbook':'#65b1ce','museum':'#195165','online':'#5198b2'};
window.venuesProperNames =     {'guestbook':'Guestbook','museum':'US Pavilion','online':'Online'};

var format = d3.time.format("%m/%d/%Y");
drawForcedGraph(networkdataFilepath, false);
d3.csv(streamdataFilepath, function(error, data) {
        data.forEach(function(d) {
            d.date = format.parse(d.date);
            d.y = parseInt(d.num_visitors) + 1;
            d.x = parseInt(d.index);
           
        });
        window.data = data;
        
        drawStreamgraph();    
});
/*******************************************************
  Show mouseover info for Space view
*******************************************************/

function showDateInfo(e,d,i){
  //+ $(e)[0].getBoundingClientRect().width)
  //$('.day-' + i + ":last").offset()["top"]
  //$(e).offset()["top"]
  var name = d.key;
  if (model == "time")
      name = window.venuesProperNames[d.key];
  d3.select('#visitor-info')
    .style("display","block");
  d3.select('#visitor-info')
    .style("top", function(d){return parseInt($(e).offset()["top"]) + "px"})
    .style("left", function(d){return parseInt($('.day-' + i + ":last").offset()["left"]) + "px"})
    .style("max-width", function(d){return parseInt($(window).width() - $('.day-' + i + ":last").offset()["left"]) + "px"})
    //.style("left", function(d){return parseInt($('.day-' + i + ":last").offset()["left"] + $(e)[0].getBoundingClientRect().width) + "px"})
    .html(d.values[i].date.toString('ddd, MMM dd, yyyy') + " - " + name + " - " + d.values[i].num_visitors + " visitors");
}
function showDayInfo(d,i){

  d3.select('#date-info')
    .style("display","block");
  d3.select('#date-info')
    .style("top", function(d){return parseInt($('.day-' + i + ":last").offset()["top"]) + "px"})
    .style("left", function(d){return parseInt($('.day-' + i + ":last").offset()["left"]) + "px"})
    .style("max-width", function(d){return parseInt($(window).width() - $('.day-' + i + ":last").offset()["left"]) + "px"})
    .html( d.values[i].date.toString('ddd, MMM dd, yyyy'));
}
function hideDateInfo(d,i){
  d3.select('#visitor-info').style("display","none");
}

/*******************************************************
  Show/Hide Network nodes
*******************************************************/
function showNetworkNodes(){
  window.networkSVG.selectAll(".node").style("fill-opacity",1.0).style("stroke-opacity",1.0);
  window.networkSVG.selectAll("text").style("fill-opacity",1.0).style("stroke-opacity",1.0);
}
function hideNetworkNodes(filterFunction){
  window.networkSVG.selectAll("text").style("fill-opacity",0.0).style("stroke-opacity",0.0);
  window.networkSVG.selectAll(".node").style("fill-opacity",0.0).style("stroke-opacity",0.0);
  window.networkSVG.selectAll(".node").filter(filterFunction).style("fill-opacity",1.0).style("stroke-opacity",1.0);
}

/*******************************************************
  DRAW SPACE STEAMGRAPH
*******************************************************/
function drawStreamgraph(){

    var nest = d3.nest()
           .key(function(d){ return d.venue});
    var n = window.data.length, // number of layers, online, guestbook & museum
       
    stack = d3.layout.stack().offset("zero")
          .values(function(d) { return d.values; });

    //group data by venue (for streamgraph)
    var layers0 = stack(nest.entries(data));

    //group data by index (for timeline)
    var dataByIndex = d3.nest()
           .key(function(d){ return d.index}).entries(data);

    //number of samples per layer
    var samples = layers0[0].values.length; 
    
    if(model =="space")
      var allValues = layers0[0].values.concat(layers0[1].values).concat(layers0[2].values).concat(layers0[3].values).concat(layers0[4].values).concat(layers0[5].values).concat(layers0[6].values);
    else
      var allValues = layers0[0].values.concat(layers0[1].values).concat(layers0[2].values);

    var yDomain = d3.max(allValues, function(d) { 
      return d.y0 + d.y; 
    });

    var width = $(window).width(),
        height = 175;

    var x = d3.scale.linear()
      .domain([0, samples - 1])
      .range([0, width]);

    var y = d3.scale.linear()
    .domain([1, yDomain])
    .range([height, 0]);

    var area = d3.svg.area()
      .x(
        function(d) { 
          return x(d.x); 
        })
        .y0(function(d) {          
          return y(d.y0); 
        })
        .y1(function(d) { 
          return y(d.y0 + d.y); 
        })
        .interpolate("cardinal")
        .tension(0.6); 

    window.streamgraphSVG = d3.select("body").append("svg")
        .attr("id", "streamgraph")
        .attr("width", width)
        .attr("height", height);

    /* Draws underlying paths */
    window.streamgraphSVG.selectAll("path")
        .data(layers0)
        .enter().append("path")
        .attr("d", function(d) { return area(d.values); })
        .attr("title", function(d) { 
          return "visitors from " + d.key;
        })
        .attr("class", function(d){ return "stream " + d.key.replace(" ", "-");})

    function highlightDay(e,d,i){
        var idx = e.id.substring(4);
        //vertical slice
        d3.selectAll('.day-' + parseInt(idx)).style("fill-opacity","1.0").style("fill","rgba(255,255,255,0.5)");
        
        //individual red selection
        d3.select(e).style("fill-opacity","1.0").style("stroke-opacity","1.0").style("fill","red");
        
        showDateInfo(e, d, idx);
    }
    function unhighlightDay(e,d,i){
        var idx = e.id.substring(4);
        d3.selectAll('.day-' + parseInt(idx)).style("fill-opacity","0.0").style("stroke-opacity","0.0");
        
    }
    function highlightStream(e,d,i){
      //horizontal stream
        d3.select("." + d.key.replace(" ", "-")).style("fill",function(){ return model == "space" ? window.continentsToColors[d.key] : window.venuesToColors[d.key]});
    }
    function unhighlightStream(e,d,i){
      d3.select(e).style("fill-opacity","0.0").style("stroke-opacity","0.0");
      d3.select("." + d.key.replace(" ", "-")).style("fill","#042c3a");
    }
    function unhighlightSelectedDay(){
        d3.selectAll('.day-' + window.highlightedDay).style("fill-opacity","0.0").style("stroke-opacity","0.0");
        d3.selectAll(".stream").style("fill","#042c3a");
        window.highlightedDay = -1;
    }
    /* Slightly hacky way to draw individually selectable days */
    for (var k=0;k<samples-1;k++){
      
      window.streamgraphSVG.selectAll("path.day")
          .data(layers0)
          .enter().append("path")
          .attr("d", function(d) { return area(new Array(d.values[k], d.values[k + 1])); })
          .attr("class", function(d) { return "stream-days day-" + k})
          .attr("id", function(d) { return "day-" + k})
          .on("mouseover", function(d, i){
            var idx = this.id.substring(4);
            highlightDay(this, d, i);
            highlightStream(this,d,i);
            var theKey = d.key;
            hideNetworkNodes(function(d,i){ 
              return model == "space" ? d.continent == theKey : d.venue == theKey;
            });
            showDateInfo(this, d, idx);
          })
          .on("mouseout", function(d, i){
            var idx = this.id.substring(4);
            if (window.highlightedDay != idx){
              unhighlightDay(this,d,i);
              unhighlightStream(this,d,i);
              showNetworkNodes();
              hideDateInfo(d,idx);
            } else{
              unhighlightStream(this,d,i);
              highlightDay(this, d, i);
              showNetworkNodes();
              hideDateInfo(d,idx);
            }
            
          })
          .on("click", function(d, i){
              var idx = this.id.substring(4);
              /*unhighlightSelectedDay();
              highlightDay(this, d, i);
              window.highlightedDay = this.id.substring(4);*/
              var visit_date = d.values[idx]["date"];
              var year = visit_date.getFullYear();
              var month = visit_date.getMonth() + 1;
              var day = visit_date.getDate();
              if (month < 10)
                month = '0' + month.toString();
              if (day < 10)
                day = '0' + day.toString();
              window.currentNetworkDate = year + '_'+ month + '_' + day;

              //showDayInfo(d, idx);
              drawForcedGraph('data/networkdata_' + model + '_' + window.currentNetworkDate + '.json', false);
          });
    }
    //select most recent day
    /*var selectedElem = '.day-' + parseInt(samples-2);
    d3.selectAll(selectedElem).style("fill-opacity","1.0").style("fill","rgba(255,255,255,0.5)");
    showDayInfo(d3.select(selectedElem).data()[0], samples-2);
    window.highlightedDay = samples-2;*/
}


/*******************************************************
  DRAW NETWORK GRAPH
*******************************************************/

function drawForcedGraph(networkdataFilepath, highlightLatestNode){

    d3.select('#forcegraph').remove();
    
    var width = $(window).width(),
    height = $(window).height()-100;

    var color = d3.scale.category20();

    window.force = d3.layout.force()
        .charge(-50)
        .linkDistance(20)
        .gravity(0.05)
        .size([width, height]);
        /*.charge(-100)
        .linkDistance(50)
        .size([width, height]);
        )*/

    window.networkSVG = d3.select("body").append("svg")
        .attr("id", "forcegraph")
        .attr("width", width)
        .attr("height", height);

    d3.json(networkdataFilepath, function(error, graph) {
      window.force
          .nodes(graph.nodes)
          .links(graph.links)
          .start();

      var link = window.networkSVG.selectAll(".link")
          .data(graph.links)
        .enter().append("line")
          .attr("class", "link")
          .style("stroke-width", function(d) { return Math.sqrt(d.value); });

      /**NEW STUFF**********/
      var node_drag = d3.behavior.drag()
        .on("dragstart", dragstart)
        .on("drag", dragmove)
        .on("dragend", dragend);

      function dragstart(d, i) {
          window.force.stop() // stops the force auto positioning before you start dragging
      }

      function dragmove(d, i) {
          d.px += d3.event.dx;
          d.py += d3.event.dy;
          d.x += d3.event.dx;
          d.y += d3.event.dy; 
          tick(); // this is the key to make it work together with updating both px,py,x,y on d !
      }

      function dragend(d, i) {
          d.fixed = true; // of course set the node to fixed so the force doesn't include the node in its auto positioning stuff
          tick();
          resumeForceGraph();
      }
      /************/

      var node = window.networkSVG.selectAll(".node")
          .data(graph.nodes)
        .enter().append("circle")
          .attr("class", "node")
          .attr("r", function(d) { 
            var r = d.is_guestbook_signer == "true" ? 10 : 5;

            return r;
          })
          .style("fill", function(d) { 
            return window.continentsToColors[d.continent];
          })
          .on("click",function(d,i){
            if(d.node_is_on){
               unhighlightNode(d3.select(this), d);
               d.node_is_on = 0;
               d.fixed = false;
            }
            else if (d.fixed){
              highlightNodeAndStayOn(d3.select(this), d);
              d.node_is_on = 1;
            }
            window.resumeForceGraph();
          })
          .on("mouseover",function(d,i){
            if (!d.fixed){
              highlightNodeAndStayOn(d3.select(this), d);
            } 
          })
          .on("mouseout",function(d,i){
            if (!d.fixed){
              unhighlightNode(d3.select(this), d);
            } 
          })
          .call(node_drag);

          //.call(window.force.drag);
      
      node.append("title")
          .text(function(d) { return d.name });
      /*node.attr("style", function(d,i){
        var hi = "hi";
      });*/
     
      var texts = window.networkSVG.selectAll("text.label")
                .data(graph.nodes)
                .enter().append("text")
                .attr("class", "network-name-label")
                .attr("id", function(d) {  return "name-label-" + d.idx})
                .text(function(d) {  return d.name;  });
      
      function highlightNodeAndFadeOut(node, d){
        
        node.transition().duration().attr("r", function(d){return d.is_guestbook_signer ? 20 : 10;})
          .style("stroke", "#CCC")
          .style("stroke-width", 2)
          .transition().delay(2000).duration(500).style("stroke-width", 0).attr("r", function(d) { return d.is_guestbook_signer ? 10 : 5;});
        d3.select("#name-label-" + d.idx).style("opacity","0.0").style("display","block").transition().duration().style("opacity","1.0").transition().delay(2000).duration(700).style("opacity", "0.0").style("display", "none");

      }
      function highlightNodeAndStayOn(node, d){
       
        node.transition().duration().attr("r", function(d){
          return d.is_guestbook_signer ? 20 : 10;
        })
          .style("stroke", "#CCC")
          .style("stroke-width", 2);
          
        d3.select("#name-label-" + d.idx).style("opacity","0.0").style("display","block").transition().duration().style("opacity","1.0");
      }
      function unhighlightNode(node, d){
        node.transition().duration(500).style("stroke-width", 0).attr("r", function(d) { return d.is_guestbook_signer ? 10 : 5;});
        d3.select("#name-label-" + d.idx).transition().delay(500).duration(700).style("opacity", "0.0").style("display", "none");
      }
      function tick() {
        link.attr("x1", function(d) { return d.source.x; })
            .attr("y1", function(d) { return d.source.y; })
            .attr("x2", function(d) { return d.target.x; })
            .attr("y2", function(d) { return d.target.y; });

        /*node.attr("cx", function(d) { return d.x; })
            .attr("cy", function(d) { return d.y; });*/
        node.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
        
        texts.attr("transform", function(d) {
          return "translate(" + (d.x + 24) + "," + (d.y + 5) + ")";
        });
      }
      window.force.on("tick", tick);
      if (highlightLatestNode){
        var latestNode = node[0][node[0].length -1];
        highlightNodeAndStayOn(d3.select(latestNode), latestNode['__data__']);
      }
    });
    //var forceInterval=self.setInterval(function(){resumeForceGraph()},5000);

    
}
function resumeForceGraph()
{
  window.force.charge((Math.floor(Math.random() * 100) + 50) * -1)
  .linkDistance(Math.floor(Math.random() * 50) + 10)
  .linkStrength(Math.random());
  window.force.start();
}
