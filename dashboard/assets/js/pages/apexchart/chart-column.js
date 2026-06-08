(function($) {
    'use strict';
    $(document).ready(function() {
        if (typeof ApexCharts === 'undefined') return;
  /* basic column chart */
        var options = {
            series: [{
                name: 'هزینه های اداری',
                data: [44, 55, 57, 56, 61, 58, 63, 60, 66]
            }, {
                name: 'هزینه های متغیر',
                data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
            }, {
                name: 'پایداری بودجه',
                data: [35, 41, 36, 26, 45, 48, 52, 53, 41]
            }],
            chart: {
                type: 'bar',
                fontFamily: "Peyda",
                height: 320
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '60%',
                    borderRadius: 2,
                },
            },
            grid: {
                borderColor: '#f2f5f7',
            },
            dataLabels: {
                enabled: false
            },
            colors: ["#735dff", "#ff5a29", "rgb(12, 199, 99)"],
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: ['ارد', 'خرد', 'تیر', 'مرد', 'شهر', 'مهر', 'آبا', 'آذر', 'دی'],
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
                title: {
                    text: '$ (هزار)',
                    style: {
                        color: "#8c9097",
                    }
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
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "$ " + val + "K"
                    }
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
        };
        var chart = new ApexCharts(document.querySelector("#column-basic"), options);
        chart.render();

        /* column chart with datalabels */
        var options = {
            series: [{
                name: 'تورم',
                data: [2.3, 3.1, 4.0, 10.1, 4.0, 3.6, 3.2, 2.3, 1.4, 0.8, 0.5, 0.2]
            }],
            chart: {
                height: 320,
                fontFamily: "Peyda",
                type: 'bar',
            },
            grid: {
                borderColor: '#f2f5f7',
            },
            plotOptions: {
                bar: {
                    columnWidth: '30%',
                    borderRadius: 3,
                    dataLabels: {
                        position: 'top', // top, center, bottom
                    },
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val + "%";
                },
                offsetY: -20,
                style: {
                    fontSize: '12px',
                    colors: ["#8c9097"]
                }
            },
            colors: ["#735dff"],
            xaxis: {
                categories: ["فرو", "ارد", "خرد", "تیر", "مرد", "شهر", "مهر", "آبا", "آذر", "دی", "بهم", "اسف"],
                position: 'top',
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                crosshairs: {
                    fill: {
                        type: 'gradient',
                        gradient: {
                            colorFrom: '#D8E3F0',
                            colorTo: '#BED1E6',
                            stops: [0, 100],
                            opacityFrom: 0.4,
                            opacityTo: 0.5,
                        }
                    }
                },
                tooltip: {
                    enabled: true,
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
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false,
                },
                labels: {
                    show: false,
                    formatter: function (val) {
                        return val + "%";
                    }
                }

            },
            title: {
                text: 'Monthly Inflation in Argentina, 2002',
                floating: true,
                offsetY: 330,
                align: 'center',
                style: {
                    color: '#444'
                }
            }
        };
        var chart = new ApexCharts(document.querySelector("#column-datalabels"), options);
        chart.render();

        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 500);
    });
})(jQuery);