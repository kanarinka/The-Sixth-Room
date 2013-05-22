/*******************************************************
  MAIN FUNCTION 
*******************************************************/
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
drawForcedGraph();
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
  d3.select('#visitor-info')
    .style("display","block");
  d3.select('#visitor-info')
    .style("top", function(d){return parseInt($(e).offset()["top"]) + "px"})
    .style("left", function(d){return parseInt($('.day-' + i + ":last").offset()["left"] + $(e)[0].getBoundingClientRect().width) + "px"})
    .html(d.key + " - " + d.values[i].num_visitors + " visitors - " +
          d.values[i].date.toString('ddd, MMM dd, yyyy'));
}
function hideDateInfo(d,i){
  d3.select('#visitor-info').style("display","none");
}
/*******************************************************
  Show/Hide Network nodes
*******************************************************/
function showNetworkNodes(){
  window.networkSVG.selectAll(".node").style("opacity",1.0);
}
function hideNetworkNodes(filterFunction){
  window.networkSVG.selectAll("text").style("display", "none");
  window.networkSVG.selectAll(".node").style("opacity",0.0);
  window.networkSVG.selectAll(".node").filter(filterFunction).style("opacity", 1.0);
}

/*******************************************************
  DRAW SPACE STEAMGRAPH
*******************************************************/
function drawStreamgraph(){

    var nest = d3.nest()
           .key(function(d){ return d.venue});
    var n = window.data.length, // number of layers, online, guestbook & museum
       
    stack = d3.layout.stack().offset("wiggle")
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
        height = 200;

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
            
            d3.selectAll('.day-' + parseInt(idx)).style("opacity","1.0").style("fill","rgba(255,255,255,0.5)");
            d3.select(this).style("opacity","1.0").style("fill","red");
            d3.select("." + d.key.replace(" ", "-")).style("fill",function(){ return model == "space" ? window.continentsToColors[d.key] : window.venuesToColors[d.key]});

            var theKey = d.key;
            hideNetworkNodes(function(d,i){ 
              return model == "space" ? d.continent == theKey : d.venue == theKey;
            });
            showDateInfo(this, d, idx);
          })
          .on("mouseout", function(d, i){
            var idx = this.id.substring(4);
            d3.selectAll('.day-' + parseInt(idx)).style("opacity","0.0");
            d3.select(this).style("opacity","0.0");
            d3.select("." + d.key.replace(" ", "-")).style("fill","#042c3a");
            showNetworkNodes();
            hideDateInfo(d,idx);
          });
    }
    
    d3.selectAll('.day-' + parseInt(samples-2)).style("opacity","0.7").style("fill","rgba(255,255,255,0.5)");
}


/*******************************************************
  DRAW NETWORK GRAPH
*******************************************************/

function drawForcedGraph(){

    var width = $(window).width(),
    height = $(window).height()-100;

    var color = d3.scale.category20();

    var force = d3.layout.force()
        .charge(-50)
        .linkDistance(10)
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
      force
          .nodes(graph.nodes)
          .links(graph.links)
          .start();

      var link = window.networkSVG.selectAll(".link")
          .data(graph.links)
        .enter().append("line")
          .attr("class", "link")
          .style("stroke-width", function(d) { return Math.sqrt(d.value); });

      var node = window.networkSVG.selectAll(".node")
          .data(graph.nodes)
        .enter().append("circle")
          .attr("class", "node")//function(d) { return "node " + "node" + d.date.toString('MMddyyyy'); })
          .attr("r", function(d) { return d.is_guestbook_signer ? 10 : 5;})
          .style("fill", function(d) { 
            return window.continentsToColors[d.continent];
            //return color(d.group); 
          })
          .on("click",function(d,i){
            d3.select(this).transition().duration(500).attr("r", function(d){return d.is_guestbook_signer ? 20 : 10;})
              .style("stroke", "#CCC")
              .style("stroke-width", 2)
              .transition().delay(10000).duration(500).style("stroke-width", 0).attr("r", function(d) { return d.is_guestbook_signer ? 10 : 5;});
            d3.select("#name-label-" + d.idx).style("opacity","0.0").style("display","block").transition().duration(700).style("opacity","1.0").transition().delay(10000).duration(700).style("opacity", "0.0").style("display", "none");
      
          })
          .call(force.drag);

      node.append("title")
          .text(function(d) { return d.name });

      var texts = window.networkSVG.selectAll("text.label")
                .data(graph.nodes)
                .enter().append("text")
                .attr("class", "network-name-label")
                .attr("id", function(d) {  return "name-label-" + d.idx})
                .text(function(d) {  return d.name + ", from " + d.continent;  });

      force.on("tick", function() {
        link.attr("x1", function(d) { return d.source.x; })
            .attr("y1", function(d) { return d.source.y; })
            .attr("x2", function(d) { return d.target.x; })
            .attr("y2", function(d) { return d.target.y; });

        node.attr("cx", function(d) { return d.x; })
            .attr("cy", function(d) { return d.y; });
        
        texts.attr("transform", function(d) {
          return "translate(" + (d.x + 24) + "," + (d.y + 5) + ")";
        });
      });
    });
}