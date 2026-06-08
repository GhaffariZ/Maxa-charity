(function($) {
    'use strict';

    $(window).on('load', function() {

        /* Discounted Products */
        var discountedProductsChartOptions = {
            series: [{
                name: 'ماهانه',
                data: [31, 40, 28, 51, 42, 109, 100]
            }],
            chart: {
                height: 320,
                type: 'area',
                fontFamily: "Peyda",
            },
            colors: ["#735dff"],
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
                        fontFamily: "Peyda",
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

        const chartEl = document.querySelector("#discountedProductsChart");

        if(chartEl) {
            var chart = new ApexCharts(chartEl, discountedProductsChartOptions);
            chart.render();
        }


        /* Sales Summary */
        var options = {
            series: [
                {
                name: "فروش",
                // 69, 58, 45, 91, 41, 62, 51, 49, 148
                data: [100, 140, 180, 90, 170, 150, 160, 220, 170, 200, 230, 280] // wave-like with final rise
            },
            {
                name: "سفارشات",
                data: [20, 180, 110, 210, 100, 130, 190, 110, 190, 250, 270, 320] // smoother, slightly opposite
            }
            ],
            chart: {
                height: 230,
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
                        fontWeight: 500
                    }
                }
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontWeight: 500,
                labels: {
                    colors: '#57575A'
                }
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function (val) {
                        return val + " واحد";
                    }
                }
            }
        };

        const salesSummaryChartEl = document.querySelector("#salesSummaryChart");

        if(salesSummaryChartEl) {
            var chart = new ApexCharts(salesSummaryChartEl, options);
            chart.render();
        }
    });

}(jQuery));