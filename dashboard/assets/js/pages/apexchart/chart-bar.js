(function($) {
    'use strict';
    $(document).ready(function() {
        if (typeof ApexCharts === 'undefined') return;

        /* basic bar chart */
        var options = {
            series: [{
                name: "سری-۱",
                data: [400, 430, 448, 470, 540, 580, 690, 1100, 1200, 1380]
            }],
            chart: {
                type: 'bar',
                fontFamily: "Peyda",
                height: 320
            },
            plotOptions: {
                bar: {
                    borderRadius: 3,
                    horizontal: true,
                }
            },
            colors: ["#735dff"],
            grid: {
                borderColor: '#f2f5f7',
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: [
                    'لوازم الکترونیکی',
                    'مد و پوشاک',
                    'لوازم آشپزخانه',
                    'محصولات آرایشی و بهداشتی',
                    'کتاب‌ها',
                    'اسباب‌بازی و بازی‌ها',
                    'پوشاک',
                    'ایالات متحده',
                    'چین',
                    'آلمان'
                ],
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
                labels: {
                    show: true,
                    style: {
                        colors: "#8c9097",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-yaxis-label',
                    },
                }
            }
        };
        var chart = new ApexCharts(document.querySelector("#bar-basic"), options);
        chart.render();

        /* grouped bar chart */
        var options = {
            series: [{
                name:"فروش",
                data: [44, 55, 41, 64, 22, 43, 21]
            }, {
                name:"درآمد",
                data: [53, 32, 33, 52, 13, 44, 32]
            }],
            chart: {
                type: 'bar',
                height: 320 
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    dataLabels: {
                        position: 'top',
                    },
                borderRadius:2,
                barHeight: 15,
                },
            },
            grid: {
                borderColor: '#f2f5f7',
            },
            colors: ["#735dff", "#ff5a29"],
            dataLabels: {
                enabled: true,
                offsetX: -6,
                style: {
                    fontSize: '10px',
                    colors: ['#fff']
                }
            },
            stroke: {
                show: true,
                width: 1,
                colors: ['#fff']
            },
            tooltip: {
                shared: true,
                intersect: false
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
            xaxis: {
                categories: [1380, 1381, 1382, 1383, 1384, 1385, 1386],
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
                labels: {
                    show: true,
                    style: {
                        colors: "#8c9097",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-yaxis-label',
                    },
                }
            }
        };
        var chart = new ApexCharts(document.querySelector("#bar-group"), options);
        chart.render();


        /* stacked bar chart */
        var options = {
            series: [{
                name: 'مارین اسپریت',
                data: [44, 55, 41, 37, 22, 43, 21]
            }, {
                name: 'پوما',
                data: [53, 32, 33, 52, 13, 43, 32]
            }, {
                name: 'نایک',
                data: [12, 17, 11, 9, 15, 11, 20]
            }, {
                name: 'اپل',
                data: [9, 7, 5, 8, 6, 9, 4]
            }, {
                name: 'سامسونگ',
                data: [25, 12, 19, 32, 25, 24, 10]
            }],
            chart: {
                type: 'bar',
                fontFamily: "Peyda",
                height: 320,
                stacked: true,
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius:2,
                },
            },
            stroke: {
                width: 1,
                colors: ['#fff']
            },
            colors: ["#735dff", "#ff5a29", "rgb(12, 199, 99)", "rgb(12, 156, 252)", "rgb(255, 154, 19)"],
            grid: {
                borderColor: '#f2f5f7',
            },
            title: {
                text: 'فروش لوازم برند',
                style: {
                    fontSize: '13px',
                    fontWeight: 'bold',
                    color: '#8c9097'
                },
            },
            xaxis: {
                categories: [2008, 2009, 2010, 2011, 2012, 2013, 2014],
                labels: {
                    formatter: function (val) {
                        return val + "K"
                    },
                    style: {
                        colors: "#8c9097",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-xaxis-label',
                    },
                }
            },
            yaxis: {
                title: {
                    text: undefined
                },
                labels: {
                    show: true,
                    style: {
                        colors: "#8c9097",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-yaxis-label',
                    },
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + "K"
                    }
                }
            },
            fill: {
                opacity: 1
            },
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                offsetX: 40,
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
            }
        };
        var chart = new ApexCharts(document.querySelector("#bar-stacked"), options);
        chart.render();


        /* bar chart with negative values */
        var options = {
            series: [{
                name: 'آقایان',
                data: [0.4, 0.65, 0.76, 0.88, 1.5, 2.1, 2.9, 3.8, 3.9, 4.2, 4, 4.3, 4.1, 4.2, 4.5,
                    3.9, 3.5, 3
                ]
            },
            {
                name: 'بانوان',
                data: [-0.8, -1.05, -1.06, -1.18, -1.4, -2.2, -2.85, -3.7, -3.96, -4.22, -4.3, -4.4,
                -4.1, -4, -4.1, -3.4, -3.1, -2.8
                ]
            }
            ],
            chart: {
                type: 'bar',
                height: 350,
                stacked: true
            },
            colors: ['#008FFB', '#FF4560'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '80%',
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 1,
                colors: ["#fff"]
            },
            colors: ["#735dff", "#ff5a29"],
            grid: {
                xaxis: {
                    lines: {
                        show: false
                    }
                }
            },
            yaxis: {
                min: -5,
                max: 5,
                title: {
                },
            },
            tooltip: {
                shared: false,
                x: {
                    formatter: function (val) {
                        return val
                    }
                },
                y: {
                    formatter: function (val) {
                        return Math.abs(val) + "%"
                    }
                }
            },
            grid: {
                borderColor: '#f2f5f7',
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
            title: {
                text: 'هرم جمعیتی موریس ۱۳۹۰',
                align: 'left',
                style: {
                    fontSize: '13px',
                    fontFamily:"Peyda",
                    fontWeight: 'bold',
                    color: '#8c9097'
                },
            },
            xaxis: {
                categories: ['85+', '80-84', '75-79', '70-74', '65-69', '60-64', '55-59', '50-54',
                    '45-49', '40-44', '35-39', '30-34', '25-29', '20-24', '15-19', '10-14', '5-9',
                    '0-4'
                ],
                title: {
                    text: 'درصد',
                    style: {
                        color: "#8c9097",
                        fontFamily: "Peyda",
                    }
                },
                labels: {
                    formatter: function (val) {
                        return Math.abs(Math.round(val)) + "%"
                    },
                    style: {
                        colors: "#8c9097",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-xaxis-label',
                    },
                }
            },
            yaxis: {
                labels: {
                    show: true,
                    style: {
                        colors: "#8c9097",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-yaxis-label',
                    },
                },
            }
        };
        var chart = new ApexCharts(document.querySelector("#bar-negative"), options);
        chart.render();

        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 500);
    });

})(jQuery);