(function($) {
    'use strict';
    function renderAllCharts() {
        if (typeof ApexCharts === 'undefined') return;

        const isRtl = $('html').attr('dir') === 'rtl' ? true : false;

        /* basic area chart */
        var options = {
            series: [{
                name: "سهام ABC",
                data: series.monthDataSeries1.prices
            }],
            chart: {
                fontFamily: "Peyda",
                type: 'area',
                height: 320,
                zoom: {
                    enabled: false
                },
                rtl: isRtl
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight',
                width: 2,
            },
            grid: {
                borderColor: '#f2f5f7',
            },
            labels: series.monthDataSeries1.dates,
            title: {
                text: ' نظر مشتری',
                align: 'left',
                style: {
                    fontSize: '13px',
                    fontWeight: 'bold',
                    color: '#8c9097'
                },
            },
            colors: ['#735dff'],
            xaxis: {
                type: 'datetime',
                reversed: isRtl,
                labels: {
                    show: true,
                    style: {
                        colors: "#8c9097",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-xaxis-label',
                    },
                }
            },
            yaxis: {
                opposite: !isRtl,
                labels: {
                    show: true,
                    style: {
                        colors: "#8c9097",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-xaxis-label',
                    },
                }
            },
            legend: {
                horizontalAlign: isRtl ? 'right' : 'left'
            }
        };
        var chart = new ApexCharts(document.querySelector("#area-basic"), options);
        chart.render();

        /* spline area chart */
        var options = {
            series: [{
                name: 'ماهانه',
                data: [31, 40, 28, 51, 42, 109, 100]
            }, {
                name: 'سالانه',
                data: [11, 32, 45, 32, 34, 52, 41]
            }],
            chart: {
                height: 320,
                type: 'area',
                fontFamily: "Peyda",
            },
            colors: ["#735dff", "#ff5a29"],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2,   
            },
            grid: {
                borderColor: '#f2f5f7',
            },
            
            xaxis: {
                type: 'datetime',
                categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"],
                labels: {
                    show: true,
                    style: {
                        colors: "#8c9097",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-xaxis-label',
                    },
                }
            },
            legend: {
                show: true,
                position: "bottom",
                offsetX: 0,
                offsetY: 8,
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
            yaxis: {
                labels: {
                    show: true,
                    style: {
                        colors: "#8c9097",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-xaxis-label',
                    },
                }
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy HH:mm'
                },
            },
        };
        var chart = new ApexCharts(document.querySelector("#area-spline"), options);
        chart.render();

    /* stacked area chart */
    var generateDayWiseTimeSeries = function (baseval, count, yrange) {
        var i = 0;
        var series = [];
        while (i < count) {
            var x = baseval;
            var y = Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;
            series.push([x, y]);
            baseval += 86400000;
            i++;
        }
        return series;
    }
    var options = {
        series: [
            {
                name: 'بهره‌وری',
                data: generateDayWiseTimeSeries(new Date('11 Feb 2017 GMT').getTime(), 20, {
                    min: 10,
                    max: 60
                })
            },
            {
                name: 'میزان حضور',
                data: generateDayWiseTimeSeries(new Date('11 Feb 2017 GMT').getTime(), 20, {
                    min: 10,
                    max: 20
                })
            },
            {
                name: 'کارایی',
                data: generateDayWiseTimeSeries(new Date('11 Feb 2017 GMT').getTime(), 20, {
                    min: 10,
                    max: 15
                })
            }
        ],
        chart: {
            type: 'area',
            height: 350,
            fontFamily: "Peyda",
            stacked: true,
            events: {
                selection: function (chart, e) {
                    console.log(new Date(e.xaxis.min))
                }
            },
        },
        colors: ['#735dff', '#ff5a29', 'rgb(12, 199, 99)'],
        grid: {
            borderColor: '#f2f5f7',
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2,
        },
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 0.2,
                opacityTo: 0.6,
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left',
            offsetX: -10,
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
        xaxis: {
            type: 'datetime'
        },
    };
    var chart = new ApexCharts(document.querySelector("#area-stacked"), options);
    chart.render();

    /* negative values chart */
    var options = {
        series: [{
            name: 'منابع',
            data: [{
                x: 1996,
                y: 322
            },
            {
                x: 1997,
                y: 324
            },
            {
                x: 1998,
                y: 329
            },
            {
                x: 1999,
                y: 342
            },
            {
                x: 2000,
                y: 348
            },
            {
                x: 2001,
                y: 334
            },
            {
                x: 2002,
                y: 325
            },
            {
                x: 2003,
                y: 316
            },
            {
                x: 2004,
                y: 318
            },
            {
                x: 2005,
                y: 330
            },
            {
                x: 2006,
                y: 355
            },
            {
                x: 2007,
                y: 366
            },
            {
                x: 2008,
                y: 337
            },
            {
                x: 2009,
                y: 352
            },
            {
                x: 2010,
                y: 377
            },
            {
                x: 2011,
                y: 383
            },
            {
                x: 2012,
                y: 344
            },
            {
                x: 2013,
                y: 366
            },
            {
                x: 2014,
                y: 389
            },
            {
                x: 2015,
                y: 334
            }
            ]
        }, {
            name: 'کارایی',
            data: [
                {
                    x: 1996,
                    y: 162
                },
                {
                    x: 1997,
                    y: 90
                },
                {
                    x: 1998,
                    y: 50
                },
                {
                    x: 1999,
                    y: 77
                },
                {
                    x: 2000,
                    y: 35
                },
                {
                    x: 2001,
                    y: -45
                },
                {
                    x: 2002,
                    y: -88
                },
                {
                    x: 2003,
                    y: -120
                },
                {
                    x: 2004,
                    y: -156
                },
                {
                    x: 2005,
                    y: -123
                },
                {
                    x: 2006,
                    y: -88
                },
                {
                    x: 2007,
                    y: -66
                },
                {
                    x: 2008,
                    y: -45
                },
                {
                    x: 2009,
                    y: -29
                },
                {
                    x: 2010,
                    y: -45
                },
                {
                    x: 2011,
                    y: -88
                },
                {
                    x: 2012,
                    y: -132
                },
                {
                    x: 2013,
                    y: -146
                },
                {
                    x: 2014,
                    y: -169
                },
                {
                    x: 2015,
                    y: -184
                }
            ]
        }],
        chart: {
            type: 'area',
            fontFamily: "Peyda",
            height: 355
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'straight',
            width: 2,
        },

        title: {
            text: 'کارایی عملیاتی',
            align: 'left',
            style: {
                fontSize: '13px',
                fontWeight: 'bold',
                color: '#8c9097'
            }
        },
        xaxis: {
            type: 'datetime',
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            labels: {
                show: true,
                style: {
                    colors: "#8c9097",
                    fontSize: '11px',
                    fontWeight: 600,
                    cssClass: 'apexcharts-xaxis-label',
                },
            }
        },
        yaxis: {
            tickAmount: 4,
            floating: false,

            labels: {
                style: {
                    colors: '#8c9097',
                    fontSize: '11px',
                    fontWeight: 600,
                    cssClass: 'apexcharts-yaxis-label',
                },
                offsetY: -7,
                offsetX: 0,
            },
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false
            }
        },
        colors: ["#735dff", "#ff5a29"],
        fill: {
            opacity: 0.5
        },
        tooltip: {
            x: {
                format: "yyyy",
            },
            fixed: {
                enabled: false,
                position: 'topRight'
            }
        },
        legend: {
            show: true,
            position: "bottom",
            offsetX: 0,
            offsetY: 8,
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
        grid: {
            borderColor: '#f2f5f7',
            yaxis: {
                lines: {
                    offsetX: -30
                }
            },
            padding: {
                left: 20
            }
        }
        };

                var el3 = document.querySelector("#area-negative");
                    if (el3) { 
                        el3.innerHTML = '';
                        new ApexCharts(el3, options).render(); 
                    }
                }

                $(document).ready(function() {
                    setTimeout(function() {
                        renderAllCharts();
                    }, 200);

                    setTimeout(function() {
                        window.dispatchEvent(new Event('resize'));
                    }, 1000);
    });
}(jQuery));