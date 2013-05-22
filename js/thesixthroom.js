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
window.continentsToColors ={  "Antarctica":"#ff7f0d", 
                              "Australia":"#1e77b4", 
                              "Asia":"#ffbb78", 
                              "Africa":"#afc6e8", 
                              "South America":"#d62628", 
                              "Europe":"#97df8a", 
                              "North America":"#2ba02b"};

var format = d3.time.format("%m/%d/%Y");
drawForcedGraph();
d3.csv(streamdataFilepath, function(error, data) {
        data.forEach(function(d) {
            d.date = format.parse(d.date);
            d.y = parseInt(d.num_visitors) + 1;
            d.x = parseInt(d.index);
           
        });
        window.data = data;
        if (model == "time")
          drawTimeStreamgraph();             
        else
          drawSpaceStreamgraph();    
});
/*******************************************************
  Show mouseover info for Space view
*******************************************************/
function updateContinentDateInfo(e, d,i){
//+ $(e)[0].getBoundingClientRect().width)
  //$('.day-' + i + ":last").offset()["top"]
  //$(e).offset()["top"]
  d3.select('#continent-visitors')
    .style("top", function(d){return parseInt($(e).offset()["top"]) + "px"})
    .style("left", function(d){return parseInt($('.day-' + i + ":last").offset()["left"] + $(e)[0].getBoundingClientRect().width) + "px"})
    .html(d.key + " - " + d.values[i].num_visitors + " visitors - " +
          d.values[i].date.toString('ddd, MMM dd, yyyy'));
}
function showContinentDateInfo(e, d,i){
  
  d3.select('#continent-visitors')
    .style("display","block");
  updateContinentDateInfo(e, d,i);
}
function hideContinentDateInfo(d,i){
  d3.select('#continent-visitors').style("display","none");
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
function drawSpaceStreamgraph(){

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
    

    var allValues = layers0[0].values.concat(layers0[1].values).concat(layers0[2].values).concat(layers0[3].values).concat(layers0[4].values).concat(layers0[5].values).concat(layers0[6].values);

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

    var color = d3.scale.linear().range(["#053749", "#6bb9d6"]);
    //LIGHT yellow green range(["#18851F", "#FAFe5b"]);
    //DARK blue .range(["#053749", "#6bb9d6"]);

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
            var continent = d.key; 
            d3.selectAll('.day-' + parseInt(idx)).style("opacity","1.0").style("fill","rgba(255,255,255,0.5)");
            d3.select(this).style("opacity","1.0").style("fill","red");
            d3.select("." + d.key.replace(" ", "-")).style("fill",window.continentsToColors[d.key]);
            hideNetworkNodes(function(d,i){ return d.continent == continent;});
            showContinentDateInfo(this, d, idx);
          })
          .on("mouseout", function(d, i){
            var idx = this.id.substring(4);
            d3.selectAll('.day-' + parseInt(idx)).style("opacity","0.0");
            d3.select(this).style("opacity","0.0");
            d3.select("." + d.key.replace(" ", "-")).style("fill","#042c3a");
            showNetworkNodes();
            hideContinentDateInfo(d,idx);
          });
    }
    
    d3.selectAll('.day-' + parseInt(samples-2)).style("opacity","0.7").style("fill","rgba(255,255,255,0.5)");
}

/*******************************************************
  DRAW TIME STEAMGRAPH
  (todo - clean this up)
*******************************************************/
function drawTimeStreamgraph(){

    
    var threeColors ={'guestbook':'#65b1ce','museum':'#195165','online':'#5198b2'};
    //'#5198b3', '#205a6f', '#38788f'
    //'#2f6d84', '#144a5e', '#5fa9c5'
    //'#65b1ce','#195165','#5198b2'
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

    var color = d3.scale.linear().range(["#053749", "#6bb9d6"]);
    //LIGHT yellow green range(["#18851F", "#FAFe5b"]);
    //DARK blue .range(["#053749", "#6bb9d6"]);

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

    window.streamgraphSVG.selectAll("path")
        .data(layers0)
        .enter().append("path")
        .attr("d", function(d) { return area(d.values); })
        .attr("id", function(d) { 
          return d.key;
        })
        .attr("title", function(d) { 
          return "visitors from " + d.key;
        })
        .attr("class", "stream");
        /*.style("fill", function() { 
          //var aColor = color(Math.random());
        
          
          return threeColors[this.id]; });*/

    //Turn Streamgraph into Interactive Timeline
    var rectData = [];
    var rectClassname = "miRect";
    var countsClassname = "miCounts";
    var lineClassname = "miLinea";
    var hlineClassname = "miLinea-h1";
    var sectionWidth = width/samples;
    
    var showCounts = function(d, i){
      $('#timeline-date').text(d.date.toString('ddd, MMM dd, yyyy'));
      $('#guestbook-visitor-count').text(d.guestbook);
      $('#museum-visitor-count').text(d.museum);
      $('#online-visitor-count').text(d.online);
      
      //position with jquery because d3 is busted for that? or maybe I was doing something wrong who knows
      //TODO - make this dynamically sized with streamgraph size, not hack sized
      $("#timeline-date").css("bottom", 23).css("left", d.x + sectionWidth + 4);
      $("#guestbook-visitors").css("bottom", 2).css("left", d.x + sectionWidth + 4);
      $("#museum-visitors").css("bottom", -30).css("left", d.x + sectionWidth + 4);
      $("#online-visitors").css("bottom", -80).css("left", d.x + sectionWidth + 4);

      d3.selectAll(".time-label").transition().duration(400).style("display", "block");
      
    }
    var hideCounts = function(d, i){
      d3.selectAll(".time-label").transition().duration(400).style("display", "none");
      
    }

    var showRect = function(d, i){

      d3.selectAll("." + rectClassname + (d.x + '').replace('.','') ).style("opacity", 0.9);
      showCounts(d,i);
      d3.select("#" + rectClassname + i).style("display", "block");
      d3.select("#" + lineClassname + i).style("display", "block");
      d3.select("#" + hlineClassname + i).style("display", "block");

      var rectDate = d.date.toString('M/d/yyyy');
      
      hideNetworkNodes(function(d,i){ 
        return d.date == rectDate; 
      })
      
    }
    var hideRect = function(d, i){
      d3.selectAll("." + rectClassname + (d.x + '').replace('.','')).style("opacity", 0.0);
      hideCounts(d,i);    
      d3.select("#" + rectClassname + i).style("display", "none");
      d3.select("#" + lineClassname + i).style("display", "none");
      d3.select("#" + hlineClassname + i).style("display", "none");
      showNetworkNodes();
     
    }
    
    //Create rect dimensions dynamically & bind to visitor data
    for (var i = 0;i<samples; i++){
      var totalVisitors = parseInt(dataByIndex[i].values[0].num_visitors) + parseInt(dataByIndex[i].values[1].num_visitors) + parseInt(dataByIndex[i].values[2].num_visitors);
      
      //three rects for each date representing each venue
      var yHeight = 0;
      var yStart = 20; //vertical offset
      var totalHeight = height - yStart;
      for (var j=0; j<3; j++){
        
        switch(j){
          case 0:
            venue = "guestbook";
            yHeight = dataByIndex[i].values[2].num_visitors/totalVisitors * totalHeight;
            break;
          case 1:
            venue = "museum";
            yStart = yStart + yHeight;
            yHeight = dataByIndex[i].values[1].num_visitors/totalVisitors * totalHeight;
            break;
          case 2:
            venue = "online";
            yStart = yStart + yHeight;
            yHeight = height - yStart;
        }

        rectData.push({"x" : i * sectionWidth, "y":yStart, "width" : sectionWidth, "height" : yHeight, "venue" : venue, "museum" : dataByIndex[i].values[1].num_visitors, "online" : dataByIndex[i].values[0].num_visitors, "guestbook" : dataByIndex[i].values[2].num_visitors, "date": dataByIndex[i].values[0].date});
      }
    }
    //Add rects to the svgContainer & bind to the data
    var rects = window.streamgraphSVG.selectAll("rect")
                   .data(rectData)
                   .enter()
                   .append("rect");

    var rectAttributes = rects
                    .attr("x", function (d) { return d.x; })
                    .attr("y", function (d) { return d.y; })
                    .attr("width", function (d) { return d.width; })
                    .attr("height", function (d) { return d.height; })
                    .attr("stroke", "#ccc")
                    .attr("stroke-width", 1)
                    .attr("fill", function (d) { return threeColors[d.venue]; })
                    .attr("class", function(d) {return rectClassname + " " + rectClassname + (d.x + '').replace('.','') });
                    


    //Line that marks where data is for each day
    var verticalLines = window.streamgraphSVG.selectAll("line." + lineClassname)
                   .data(rectData)
                   .enter()
                   .append("line");

    var lineAttributes = verticalLines
                    .attr("x1", function (d) { return d.x + sectionWidth; })
                    .attr("y1", function (d) { return 0; })
                    .attr("x2", function (d) { return d.x + sectionWidth; })
                    .attr("y2", function (d) { return height; })
                    .attr("id", function(d,i) { return lineClassname + i })
                    .attr("class", lineClassname);
                    
    //Line that marks where data is for each day
    var horizontalLines = window.streamgraphSVG.selectAll("line." + hlineClassname)
                   .data(rectData)
                   .enter()
                   .append("line");

    var hlineAttributes = horizontalLines
                    .attr("x1", function (d) { return d.x + sectionWidth; })
                    .attr("y1", function (d) { return 20; })
                    .attr("x2", function (d) { return width; })
                    .attr("y2", function (d) { return 20; })
                    .attr("id", function(d,i) { return hlineClassname + i })
                    .attr("class", hlineClassname);

    d3.selectAll("rect").on("mouseover", showRect)
                        .on("mouseout", hideRect);
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
            d3.select(this).attr("r", function(d){return d.is_guestbook_signer ? 20 : 10;})
              .style("stroke", "#CCC")
              .style("stroke-width", 2)
              .transition().delay(10000).duration(500).style("stroke-width", 0).attr("r", function(d) { return d.is_guestbook_signer ? 10 : 5;});
            d3.select("#name-label-" + d.idx).style("display","block").transition().delay(10000).duration(500).style("display", "none");
      
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