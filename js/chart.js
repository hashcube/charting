Array.prototype.min = function() {
  var min = this[0];
  for(var i=0; i < this.length; i++){
    if(this[i] < min)
      min = this[i];
  }
  return min;
}

Array.prototype.max = function() {
  var max = this[0];
  for(var i=0; i < this.length; i++){
    if(this[i] > max)
      max = this[i];
  }
  return max;
}

Number.prototype.addCommas = function() {
  var parts = (this + '').split('.');
  var int_part = parts[0];
  var float_part = parts.length > 1 ? '.' + parts[1] : ''; 
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(int_part)) {
    int_part = int_part.replace(rgx, '$1' + ',' + '$2');
  }
  return int_part + float_part;
}

$(document).ready(function() {

  var chartNavItem  = _.template($('#chart-nav-item').html());
  _.each(data, function(chart) {
    var chartid = chart.title;
    $('#chart_selector').append(chartNavItem({
      "chartid": chartid
    }));
    chartel_id = createDiv(chartid);
    if(chart.chart_type == "pie")
      createPieChart(chartel_id, chart);
    else
      createChart(chartel_id, chart);
  });

  if(ltv) {
    writeLTVData();
  }
  
  if(paying_users_segmntd) {
   writePayingUsersSegmentedData();
  }
});

function createDiv(chartid) {
  var chartel_id = chartid;
  var el = $("<div></div>");
  el.attr('id',chartel_id);
  el.css('margin', '20px');
  $('body').append(el);
  return chartel_id;
}

function createPieChart(el, data) {
  var chart0_info = data.charts[0];
  var series_data = Array();
  _.each(data.charts, function(d) {
    var obj = {
      type : "pie",
      name: d.y_axis
    };
    var vals = Array();
    if(d.result.x == null)
      return;

    // If pie chart has more than 20 sectors, (keep top 20 sectors and
    // create a 'Others' sector as the 21st one) move the rest into 
    // Others sector
    if(d.result.x.length > 20) {
      var others = 0;
      while(d.result.x.length > 20) {
        d.result.x.pop();
        others += d.result.y.pop();
      }
      d.result.x[20] = "Others";
      d.result.y[20] = others;
    }

    for(var i=0; i<d.result.x.length; i++){
      vals[i] = Array(chart0_info.result.x[i],
                      d.result.y[i]);
    }
    obj.data = vals;
    series_data.push(obj);
  });
  var chartid = new Highcharts.Chart({
    chart: {
      renderTo: el
    },
    title: {
      text: data.title
    },
    tooltip: {
      formatter: function() {
        return '<b>'+ this.point.name +'</b>: '+ this.percentage.toFixed(2) +' %';
      }
    },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
          enabled: true,
          formatter: function() {
            return '<b>'+ this.point.name +'</b>: '+ this.point.y;
          }
        }
      }
    },
    series: series_data
  });
  return chartid;
}

function createChart(el, data) {
  var chart0_info = data.charts[0];
  var series_data = Array();
  _.each(data.charts, function(d) {
    var obj = {
      name: d.y_axis,
      data: _.values(d.result.y),
      type: d.type
    };
    if(d.type!=null && d.type.search(/stack/) >= 0){
      obj.type= '';
      var i = d.type.search(/=/);
      obj.stack = d.type.slice(i+1);
    }
    //obj.pointStart = Date.UTC(2011,10,21);
    //obj.pointInterval = 24*3600*1000;
    series_data.push(obj);
  });

  var chartoptions = {
    chart: {
      renderTo: el,
      alignTicks: false,
      zoomType: 'x',
      events : {
        selection: function(event) {
          //this.toolbar.remove('alltime');
          if(event.yAxis) {
            var min = event.yAxis[0].min;
            var max = event.yAxis[0].max;
            //console.log(event.yAxis[0]);
            //console.log(event.xAxis[0]);
            //min = findMin(series_data);
            this.yAxis[0].setExtremes(min, max);
          }
        },
        load: function(event) {
          var max = this.xAxis[0].getExtremes().dataMax;
          var min = (max-31>0) ? max-31 : 0;
          this.xAxis[0].setExtremes(min, max);
          var chart = this;
          var remove = function() {
            chart.xAxis[0].setExtremes(null,null);
            //chart.toolbar.remove('alltime');
          };
          //this.toolbar.add('alltime','All Time','reset zoom', remove);
        }
      },
    },
    title: {
      text: data.title
    },
    xAxis: {
      title: {
        text: data.x_axis
      },
      categories: _.values(chart0_info.result.x),
      //type: 'datetime',
      //tickInterval: 24*3600*1000,
      labels: {
        rotation: -45,
        align: "right"
      }
    },
    yAxis: {
      startOnTick: false,
      endOnTick: false,
      maxPadding: '0.3',
      title: {
        text: data.y_axis
      }
    },
    series: series_data
  };

  if(data.chart_type == 'stack column') {
    chartoptions.plotOptions = {
      column: {
        stacking: 'normal'
      }
    };
    chartoptions.tooltip = {
      formatter: function() {
        return '<b>Date: '+ this.x +'</b><br/>'+
          this.series.name +': '+ this.y.addCommas() +'<br/>'+
          'Total: '+ this.point.stackTotal.addCommas() + '<br/>'+
          '%: '+ this.point.percentage.toFixed(2) +'%';
      }
    };
    chartoptions.chart.type = 'column';
  }
  else if(data.chart_type == 'combination') {
    chartoptions.chart.type = '';
  }
  else if(data.chart_type == 'line'){
    chartoptions.tooltip = {
      formatter : function() {
        return '<b>Date: ' + this.x + '<b><br/>' + this.series.name + ': ' + this.y.addCommas();
      }
    };

    chartoptions.chart.defaultSeriesType = 'line';
  }
  else {
    chartoptions.chart.type = data.chart_type;
  }

  var chartid = new Highcharts.Chart(chartoptions);
  return chartid;
}

function findMin(series){
  var Min = series[0].data.min();
  _.each(series, function(obj){
    if(obj.data.min() < Min) {
      Min = obj.data.min();
    }
  });
  return Min;
}
function findMax(series){
  var Max = series[0].data.max();
  _.each(series, function(obj){
    if(obj.data.max() > Max) {
      Max = obj.data.max();
    }
  });
  return Max;
}

function writeLTVData(){
  var ltvBody  = _.template($('#ltv-tbody').html());
  var ltvRow  = _.template($('#ltv-row').html());
  var ltvItem  = _.template($('#ltv-item').html());

  $('body').append("<table class='zebra-striped' id='ltv' style='width:auto;margin:0 auto;'></table>");
  $('#ltv').html('<thead><tr><th>SNo</th><th>Gold</th><th>LTV</th><th>uid</th><th>Country</th><th>Gender</th><th>Source</th><th>Starttime</th><th>Lasttime</th><th>MaxMilestone</th></tr></thead>');

  var tbody = "";
  var s_no = 0;
  _.each(ltv, function(entry){
    var ltv_item="";
    s_no++;
    updated_entry = getUpdatedLTVObject(s_no, entry);
    _.each(updated_entry, function(vals){
      ltv_item += ltvItem({"val": vals });
    });
    tbody += ltvRow({"row": ltv_item });
  });
  $('#ltv').append(ltvBody({"tbody": tbody }));
  $("#ltv").tablesorter();
}

function getUpdatedLTVObject(s_no, entry) {
  new_entry = new Object();
  new_entry.index = s_no;
  for(var i in entry) {
    new_entry[i] = entry[i];
  }
  return new_entry;
}

var graph_count = 0;

function addChart() {
  var chart_template = _.template($('#addChart-template').html());
  $('#addChart-popup').html(chart_template());

  // Opening the add chart dialog
  $('#addChart-popup').dialog({
    width: 700,
    minHeight: 300,
    position: [300, 100],
    beforeClose: function() {
      graph_count = 0;
    },
    modal: true
  });

  // Adding graphs to each chart
  $('#addGraph').click(function() {
    var graph_template = _.template($('#addGraphToChart-template').html());
    $('#chart-graphs').append(graph_template({num: graph_count}));
    graph_count++;
  });

  // Save the chart, when click on save
  $('#saveChart').click(function() {
    // Gather the details regarding the chart to be saved
    var chartid= $('input[name="chartid"]').val();
    var title= $('input[name="title"]').val();
    var chart_type= $('input[name="chart_type"]').val();
    var x_axis= $('input[name="x_axis"]').val();
    var y_axis= $('input[name="y_axis"]').val();
    var query = new Array();
    var y_axis_graph = new Array();
    var type_graph = new Array();
    for(var i=0;i<graph_count;i++) {
      query[i] = $('#graph'+i+' textarea[name="query"]').val();
      y_axis_graph[i] = $('#graph'+i+' input[name="y_axis_graph"]').val();
      type_graph[i] = $('#graph'+i+' input[name="type_graph"]').val();
    }
    query = JSON.stringify(query);
    y_axis_graph = JSON.stringify(y_axis_graph);
    type_graph = JSON.stringify(type_graph);

    // Prepare the datastring to POST in ajax req
    var dataString = "chartid="+chartid+"&title="+title+"&chart_type="+chart_type+
      "&x_axis="+x_axis+"&y_axis="+y_axis+"&query="+query+"&y_axis_graph="+y_axis_graph+
      "&type_graph="+type_graph+"&tabid="+tabid;

    // Make ajax req
    $.ajax({
      type: 'POST',
      data: dataString,
      url: APPURL+"ajax/chart/saveChart",
      success: function() {
        $('#addChart-popup').dialog('close');
        $('#notification').html('Chart Saved');
        $.delay(1000, function() {
          $('#notification').html('');
        });
      }
    });
  });
}

function writePayingUsersSegmentedData(){
  var body  = _.template($('#paying-users-segmntd-tbody').html());
  var row  = _.template($('#paying-users-segmntd-row').html());
  var item  = _.template($('#paying-users-segmntd-item').html());

  $('body').append("<table class='zebra-striped' id='paying-users-segmntd' style='width:auto;margin:0 auto;'></table>");
  $('#paying-users-segmntd').html('<thead>' +
                                    '<tr><th>Paying Users Segmented Data</th></tr>' +
                                     '<tr>' + 
                                        '<th>MaxMilestone</th>' + 
                                        '<th>Starttime</th>' +
                                        '<th>Date</th>' +
                                        '<th>Gender</th>' + 
                                        '<th>Country</th>' +
                                        '<th>Amount</th>' +
                                        '<th>Source</th>' +
                                        '<th>Uid</th>' +
                                     '</tr>' +
                                   '</thead>');

  var tbody = "";
  _.each(paying_users_segmntd, function(entry){
    var sgmntd_item="";
    _.each(entry, function(vals){
      sgmntd_item += item({"val": vals });
    });
    tbody += row({"row": sgmntd_item });
  });
  $('#paying-users-segmntd').append(body({"tbody": tbody }));
  $("#paying-users-segmntd").tablesorter();
}
