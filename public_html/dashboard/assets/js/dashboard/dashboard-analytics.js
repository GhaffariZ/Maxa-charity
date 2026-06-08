(function($) {
    'use strict';
    function renderAllCharts() {
        if (typeof ApexCharts === 'undefined') return;
    
        /* basic column chart */
        var options = {
            series: [
                {
                    name: 'درآمد',
                    type: 'column',
                    data: [44, 55, 57, 56, 61, 68, 72, 80, 91, 98, 105, 120]
                },
                {
                    name: 'سفارشات',
                    type: 'column',
                    data: [320, 380, 410, 390, 430, 460, 490, 530, 580, 610, 640, 680]
                },
                {
                    name: 'کاربر فعال',
                    type: 'line',
                    data: [520, 610, 680, 640, 700, 740, 790, 860, 910, 980, 1040, 1110]
                }
            ],
            chart: {
                height: 330,
                type: 'line',
                fontFamily: "Peyda",
                toolbar: {
                    show: false
                }
            },
            stroke: {
                width: [0, 0, 3],
                curve: 'smooth'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '45%',
                    fontFamily: "Peyda",
                    borderRadius: 5,
                },
            },
            dataLabels: {
                enabled: true,
                enabledOnSeries: [2],
                formatter: function (val) {
                    return val;
                },
                style: {
                    fontSize: '11px',
                    fontFamily: "Peyda",
                    colors: ['#555']
                }
            },
            colors: ['#735dff', '#ff5a29', '#00caab'],
            labels: ['فرو', 'ارد', 'خرد', 'تیر', 'مرد', 'شهر', 'مهر', 'آبا', 'آذر', 'دی', 'بهم', 'اسف'],
            xaxis: {
                labels: {
                    style: {
                        colors: "#8c9097",
                        fontSize: '11px',
                        fontFamily: "Peyda",
                        fontWeight: 600,
                    }
                }
            },
            yaxis: [
                {
                    title: {
                        text: 'درآمد (هزار دلار)',
                        style: { color: "#8c9097", fontFamily: "Peyda", }
                    },
                    labels: {
                        style: {
                            colors: "#8c9097",
                            fontFamily: "Peyda",
                            fontSize: '11px',
                            fontWeight: 600,
                        }
                    }
                },
                {
                    opposite: true,
                    title: {
                        text: 'کاربر فعال',
                        style: { color: "#8c9097", fontFamily: "Peyda", }
                    },
                    labels: {
                        style: {
                            colors: "#8c9097",
                            fontFamily: "Peyda",
                            fontSize: '11px',
                            fontWeight: 600,
                        }
                    }
                }
            ],
            tooltip: {
                shared: true,
                intersect: false,
                y: [
                    {
                        formatter: function (val) {
                            return "$" + val + "k";
                        }
                    },
                    {
                        formatter: function (val) {
                            return val + " سفارش";
                        }
                    },
                    {
                        formatter: function (val) {
                            return val + " کاربر";
                        }
                    }
                ]
            },
            legend: {
                show: true,
                position: 'bottom',
                fontFamily: "Peyda",
                offsetY: 6,
                fontSize: '12px',
                fontWeight: 600,
            },
            grid: {
                borderColor: '#f2f5f7',
            }
        };

        var chart = new ApexCharts(document.querySelector("#analytics-overview"), options);
        chart.render();


        var sourceOptions = {
            series: [35, 48, 27, 19, 22],
            labels: ['مستقیم', 'سرچ ارگانیک', 'سوشال مدیا', 'ایمیل', 'معرفی‌شده'],
            chart: {
                type: 'polarArea',
                fontFamily: "Peyda",
                height: 300
            },
            stroke: {
                colors: ['#fff']
            },
            fill: {
                opacity: 0.85
            },
            colors: ["#735dff", "#ff5a29", "rgb(12, 199, 99)", "#0ca3e7", "rgb(255, 154, 19)"],
            legend: {
                position: 'bottom',
                fontSize: '12px',
                fontFamily: "Peyda",
                fontWeight: 600,
                labels: {
                    colors: '#8c9097'
                },
                markers: {
                    size: 4,
                    strokeWidth: 0,
                    radius: 12,
                    offsetY: 0
                },
            },
            grid: {
                borderColor: '#f2f5f7',
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            tooltip: {
                y: {
                    formatter: function(val, opts) {
                        return "% از ترافیک کلی" + val;
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#polararea-basic"), sourceOptions);
        chart.render();

        
        var options = {
            series: [
                {
                    name: 'جلسات',
                    type: 'column',
                    data: [440, 505, 414, 671, 227, 413, 201, 352, 752, 320, 257, 160]
                }, 
                {
                    name: 'بازدید',
                    type: 'column',
                    data: [340, 270, 540, 130, 470, 220, 430, 220, 430, 220, 430, 220]
                },
                {
                    name: 'فروش',
                    type: 'line',
                    data: [520, 430, 320, 550, 430, 327, 540, 310, 540, 350, 540, 320]
                }
            ],
            chart: {
                height: 320,
                type: 'line',
                fontFamily: "Peyda",
                align: "right",
            },
            stroke: {
                width: [0, 2],
                curve: 'smooth'
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
                text: 'آنالیزهای وبسایت',
                fontFamily: "Peyda",
                align: 'left',
                style: {
                    fontSize: '13px',
                    fontFamily: "Peyda",
                    fontWeight: 'bold',
                    color: '#8c9097'
                },
            },
            dataLabels: {
                enabled: true,
                enabledOnSeries: [2]
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
                        fontFamily: "Peyda",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-xaxis-label',
                    },
                }
            },
            yaxis: [{
                title: {
                    text: 'جلسات',
                    style: {
                        color: "#8c9097",
                        fontFamily: "Peyda",
                    }
                },
                labels: {
                    show: true,
                    style: {
                        colors: "#8c9097",
                        fontFamily: "Peyda",
                        fontSize: '11px',
                        fontWeight: 600,
                        cssClass: 'apexcharts-yaxis-label',
                    },
                }
            }, {
                opposite: true,
                title: {
                    text: 'بازدید',
                    style: {
                        color: "#8c9097",
                        fontFamily: "Peyda",
                    }
                }
            }]
        };
        var el3 = document.querySelector("#analytics-mixed-chart");
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
    }
)(jQuery);