(function($) {
    'use strict';
    $(document).ready(function() {
        if (typeof ApexCharts === 'undefined') return;

    
        /* basic pie chart */
        var options = {
            series: [44, 55, 13, 43, 22],
            chart: {
              height: 300,
              fontFamily: "Peyda",
              type: "pie",
            },
            colors: ["#735dff", "#ff5a29", "rgb(12, 199, 99)", "#0ca3e7", "rgb(255, 154, 19)"],
            labels: ["تیم ۱", "تیم ۲", "تیم ۳", "تیم ۴", "تیم ۵"],
            legend: {
              position: "bottom",
                markers: {
                    size: 4,
                    strokeWidth: 0,
                    strokeColor: '#fff',
                    fillColors: undefined,
                    radius: 12,
                    customHTML: undefined,
                    onClick: undefined,
                    offsetX: 0,
                    offsetY: 0
                },
            },
            dataLabels: {
              dropShadow: {
                enabled: false,
              },
            },
          };
          var chart = new ApexCharts(document.querySelector("#pie-basic"), options);
          chart.render();
          
          /* simple donut chart */
          var options = {
            series: [44, 55, 41, 17, 15],
            chart: {
              type: "donut",
              fontFamily: "Peyda",
              height: 290,
            },
            labels: ["سری ۱", "سری ۲", "سری ۳", "سری ۴", "سری ۵"],
            legend: {
              position: "bottom",
                markers: {
                    size: 4,
                    strokeWidth: 0,
                    strokeColor: '#fff',
                    fillColors: undefined,
                    radius: 12,
                    customHTML: undefined,
                    onClick: undefined,
                    offsetX: 0,
                    offsetY: 0
                },
            },
            colors: ["#735dff", "#ff5a29", "rgb(12, 199, 99)", "#0ca3e7", "rgb(255, 154, 19)"],
            dataLabels: {
              dropShadow: {
                enabled: false,
              },
            },
          };
          var chart = new ApexCharts(document.querySelector("#donut-simple"), options);
                chart.render();
                setTimeout(function() {
                    window.dispatchEvent(new Event('resize'));
                }, 500);
    });
})(jQuery);