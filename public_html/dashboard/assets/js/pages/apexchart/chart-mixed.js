(function () {
    "use strict";
    $(document).ready(function() {
        if (typeof ApexCharts === 'undefined') return;

            /* line&column chart */
            var options = {
                series: [{
                    name: 'مقابه وبسایت',
                    type: 'column',
                    data: [440, 505, 414, 671, 227, 413, 201, 352, 752, 320, 257, 160]
                }, {
                    name: 'سوشال مدیا',
                    type: 'line',
                    data: [23, 42, 35, 27, 43, 22, 17, 31, 22, 22, 12, 16]
                }],
                chart: {
                    height: 320,
                    fontFamily: "Peyda",
                    type: 'line',
                },
                stroke: {
                    width: [0, 2]
                },
                grid: {
                    borderColor: '#f2f5f7',
                },
                plotOptions: {
                    bar: {
                    columnWidth: '35%',
                    borderRadius: [2],
                    }
                },
                title: {
                    text: 'ترافیک منابع',
                    align: 'left',
                    style: {
                        fontSize: '13px',
                        fontWeight: 'bold',
                        color: '#8c9097'
                    },
                },
                dataLabels: {
                    enabled: true,
                    enabledOnSeries: [1]
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
                colors: ["#735dff", "#ff5a29"],
                labels: ['01 Jan 2001', '02 Jan 2001', '03 Jan 2001', '04 Jan 2001', '05 Jan 2001', '06 Jan 2001', '07 Jan 2001', '08 Jan 2001', '09 Jan 2001', '10 Jan 2001', '11 Jan 2001', '12 Jan 2001'],
                xaxis: {
                    type: 'datetime',
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
                yaxis: [{
                    title: {
                        text: 'مقاله وبسایت',
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
                            cssClass: 'apexcharts-yaxis-label',
                        },
                    }
                }, {
                    opposite: true,
                    title: {
                        text: 'سوشال مدیا',
                        style: {
                            color: "#8c9097",
                        }
                    }
                }]
            };
            var chart = new ApexCharts(document.querySelector("#mixed-linecolumn"), options);
            chart.render();

            /* multiple ys-axis chart */
            var options = {
                series: [{
                    name: 'درآمد',
                    type: 'column',
                    data: [1.4, 2, 2.5, 1.5, 2.5, 2.8, 3.8, 4.6]
                }, {
                    name: 'مخارج',
                    type: 'column',
                    data: [1.1, 3, 3.1, 4, 4.1, 4.9, 6.5, 8.5]
                }, {
                    name: 'سود',
                    type: 'line',
                    data: [20, 29, 37, 36, 44, 45, 50, 58]
                }],
                chart: {
                    height: 320,
                    fontFamily: "Peyda",
                    type: 'line',
                    stacked: false
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: [1, 1, 2]
                },
                plotOptions: {
                    bar: {
                    columnWidth: '45%',
                    borderRadius: [2],
                    }
                },
                title: {
                    text: 'آنالیز موجودی (سال ۱۳۸۸تا۱۳۹۵)',
                    align: 'left',
                    offsetX: 110,
                    style: {
                        fontSize: '13px',
                        fontWeight: 'bold',
                        color: '#8c9097'
                    },
                },
                grid: {
                    borderColor: '#f2f5f7',
                },
                colors: ["#735dff", "#ff5a29", "rgb(12, 199, 99)"],
                xaxis: {
                    categories: [1388, 1389, 1390, 1391, 1392, 1393, 1394, 1395],
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
                yaxis: [
                    {
                        axisTicks: {
                            show: true,
                        },
                        axisBorder: {
                            show: true,
                            color: '#735dff'
                        },
                        labels: {
                            style: {
                                colors: '#735dff',
                            }
                        },
                        title: {
                            text: "درآمد",
                            style: {
                                color: '#735dff',
                            }
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    {
                        seriesName: 'Income',
                        opposite: true,
                        axisTicks: {
                            show: true,
                        },
                        axisBorder: {
                            show: true,
                            color: '#ff5a29'
                        },
                        labels: {
                            style: {
                                colors: '#ff5a29',
                            }
                        },
                        title: {
                            text: "سود عملیاتی",
                            style: {
                                color: '#ff5a29',
                            }
                        },
                    },
                    {
                        seriesName: 'درآمد',
                        opposite: true,
                        axisTicks: {
                            show: true,
                        },
                        axisBorder: {
                            show: true,
                            color: '#f4a742'
                        },
                        labels: {
                            style: {
                                colors: '#f4a742',
                            },
                        },
                        title: {
                            text: "Revenue (thousand crores)",
                            style: {
                                color: '#f4a742',
                            }
                        }
                    },
                ],
                tooltip: {
                    fixed: {
                        enabled: true,
                        position: 'topLeft', // topRight, topLeft, bottomRight, bottomLeft
                        offsetY: 30,
                        offsetX: 60
                    },
                },
                legend: {
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
            var chart = new ApexCharts(document.querySelector("#mixed-multiple-y"), options);
            chart.render();

            /* line and area chart */
            var options = {
                series: [{
                    name: 'تیم ۱',
                    type: 'area',
                    data: [44, 55, 31, 47, 31, 43, 26, 41, 31, 47, 33]
                }, {
                    name: 'تیم ۲',
                    type: 'line',
                    data: [55, 69, 45, 61, 43, 54, 37, 52, 44, 61, 43]
                }],
                chart: {
                    height: 320,
                    fontFamily: "Peyda",
                    type: 'line',
                },
                stroke: {
                    curve: 'smooth',
                    width: 2,
                },
                colors: ["#735dff", "#ff5a29"],
                grid: {
                    borderColor: '#f2f5f7',
                },
                fill: {
                    type: 'solid',
                    opacity: [0.1, 1],
                },
                labels: ['بهم 01', 'بهم 02', 'بهم 03', 'بهم 04', 'بهم 05', 'بهم 06', 'بهم 07', 'بهم 08', 'بهم 09 ', 'بهم 10', 'بهم 11'],
                markers: {
                    size: 0
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
                yaxis: [
                    {
                        title: {
                            text: 'سری اول',
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
                                cssClass: 'apexcharts-yaxis-label',
                            },
                        }
                    },
                    {
                        opposite: true,
                        title: {
                            text: 'سری دوم',
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
                                cssClass: 'apexcharts-yaxis-label',
                            },
                        }
                    },
                ],
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function (y) {
                            if (typeof y !== "undefined") {
                                return y.toFixed(0) + " عدد";
                            }
                            return y;
                        }
                    }
                }
            };
            var chart = new ApexCharts(document.querySelector("#mixed-linearea"), options);
            chart.render();

            /* line column and area chart */
            var options = {
                series: [{
                    name: 'تیم ۱',
                    type: 'column',
                    data: [23, 11, 22, 27, 13, 22, 37, 21, 44, 22, 30]
                }, {
                    name: 'تیم ۲',
                    type: 'area',
                    data: [44, 55, 41, 67, 22, 43, 21, 41, 56, 27, 43]
                }, {
                    name: 'تیم ۳',
                    type: 'line',
                    data: [30, 25, 36, 30, 45, 35, 64, 52, 59, 36, 39]
                }],
                chart: {
                    height: 320,
                    fontFamily: "Peyda",
                    type: 'line',
                    stacked: false,
                },
                stroke: {
                    width: [0, 2, 2],
                    curve: 'smooth'
                },
                plotOptions: {
                    bar: {
                        columnWidth: '30%',
                        borderRadius: 2,
                    }
                },
                colors: ["#735dff","#ff5a29","rgb(12, 199, 99)"],
                grid: {
                    borderColor: '#f2f5f7',
                },
                fill: {
                    opacity: [0.85, 0.1, 1],
                    gradient: {
                        inverseColors: false,
                        shade: 'light',
                        type: "vertical",
                        opacityFrom: 0.85,
                        opacityTo: 0.55,
                        stops: [0, 100, 100, 100]
                    }
                },
                labels: ['01/01/2003', '02/01/2003', '03/01/2003', '04/01/2003', '05/01/2003', '06/01/2003', '07/01/2003',
                    '08/01/2003', '09/01/2003', '10/01/2003', '11/01/2003'
                ],
                markers: {
                    size: 0
                },
                xaxis: {
                    type: 'datetime',
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
                    title: {
                        text: 'نمرات',
                        style: {
                            color: "#8c9097",
                        }
                    },
                    min: 0,
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
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function (y) {
                            if (typeof y !== "undefined") {
                                return y.toFixed(0) + " نمره";
                            }
                            return y;

                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#mixed-all"), options);
                chart.render();

                setTimeout(function() {
                    window.dispatchEvent(new Event('resize'));
                }, 500);
    });
})(jQuery);