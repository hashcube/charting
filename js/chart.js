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
  _.each(data.charts, function(d){
    var obj = {
      type : "pie",
      name: d.y_axis
    };
    var vals = Array();
    if(d.result.x == null)
      return;
    var max_pies = (d.result.x.length>20) ? 20 : d.result.x.length;
    for(var i=0; i<max_pies; i++){
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
            console.log(event.yAxis[0]);
            console.log(event.xAxis[0]);
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
      type: data.chart_type
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
        return '<b>'+ this.x +'</b><br/>'+
          this.series.name +': '+ this.y +'<br/>'+
          'Total: '+ this.point.stackTotal + '<br/>'+
          '%: '+ this.point.percentage.toFixed(2) +'%';
      }
    };
    chartoptions.chart.type = 'column';
  }
  else if(data.chart_type == 'combination') {
    chartoptions.chart.type = '';
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
  $('#ltv').html('<thead><tr><th>Gold</th><th>LTV</th><th>uid</th><th>Country</th><th>Gender</th><th>Source</th><th>Starttime</th><th>Lasttime</th><th>MaxMilestone</th></tr></thead>');

  var tbody = "";
  _.each(ltv, function(entry){
    var ltv_item="";
    _.each(entry, function(vals){
      ltv_item += ltvItem({"val": vals });
    });
    tbody += ltvRow({"row": ltv_item });
  });
  $('#ltv').append(ltvBody({"tbody": tbody }));
  $("#ltv").tablesorter();
}