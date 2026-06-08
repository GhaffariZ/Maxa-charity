(function($) {
    'use strict';


    $(window).on('load', function() {
        /* Sales Summary */
        var options = {
            series: [
                {
                name: "فروش",
                data: [100, 140, 40, 70, 130, 150, 160, 220, 60, 200, 230, 280]
            },
            {
                name: "سفارشات",
                data: [20, 180, 110, 80, 50, 110, 190, 110, 40, 250, 270, 320]
            }
            ],
            chart: {
                height: 290,
                type: 'line',
                fontFamily: "Peyda",
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            colors: ['#5F4AFE', '#FF9500'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            
            grid: {
                borderColor: '#E7E7EA',
                strokeDashArray: 4
            },
            xaxis: {
                categories: ['فرو', 'ارد', 'خرد', 'تیر', 'مرد', 'شهر', 'مهر', 'آبا', 'آذر', 'دی', 'بهم', 'اسف'],
                labels: {
                    show: true,
                    style: {
                        colors: "#939397",
                        fontSize: '12px',
                        fontFamily: "Peyda",
                        fontWeight: 500
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                min: 0,
                labels: {
                    show: true,
                    style: {
                        colors: "#939397",
                        fontSize: '12px',
                        fontWeight: 500,
                        fontFamily: "Peyda",
                    }
                }
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontWeight: 500,
                fontFamily: "Peyda",
                labels: {
                    colors: '#57575A'
                }
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function (val) {
                        return val + " ";
                    }
                }
            }
        };

        const revenueOverviewChartEl = document.querySelector("#revenueOverviewChart");

        if(revenueOverviewChartEl){
            var chart = new ApexCharts(revenueOverviewChartEl, options);
            chart.render();
        }

        var optionsChart = {
            series: [44, 55, 13, 43, 22, 10, 5],
            chart: {
                width: 380,
                type: 'donut',
                fontFamily: "Peyda",
            },
            labels: ['تکنولوژی', 'هوش مصنوعی', 'کسب و کار', 'عکاسی', 'طراحی', 'بازاریابی', 'موسیقی'],
            colors: ['#306795', '#0DD9C8', '#e7508fff', '#FD8865', '#8073D8', '#d3661dff', '#FFBA13'],
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toFixed(1) + "%";
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'مجموع ثبت نام',
                                fontSize: '14px',
                                fontWeight: 700,
                                color: '#5F4AFE',
                                fontFamily: "Peyda",
                                formatter: function (w) {
                                    const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return sum;
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                fontSize: '13px',
                fontFamily: "Peyda",
                fontWeight: 500,
                labels: { colors: '#8c9097' },
                markers: { width: 10, height: 10, radius: 12 }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "ثبت نام " + val;
                    }
                }
            },
            responsive: [
                {
                    breakpoint: 480,
                    options: {
                        chart: { width: 250 },
                        legend: { position: 'bottom' }
                    }
                }
            ]
        };



        const chartEl = document.querySelector("#categoryChart");

        if(chartEl) {
            var chart = new ApexCharts(chartEl, optionsChart);
            chart.render();
        }
    });
      
      
    


}(jQuery));