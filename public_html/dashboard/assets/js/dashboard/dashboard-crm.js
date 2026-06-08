(function($) {
    'use strict';
    function renderAllCharts() {
        if (typeof ApexCharts === 'undefined') return;

        const shuffle = (arr) => [...arr].sort(() => Math.random() - 0.5);

        // Dataset
        const dataset = [47, 45, 54, 38, 56, 24, 65, 31, 37, 39, 62, 51, 35, 41, 35, 27, 93, 53, 61, 27, 54, 43, 19, 46];

        const labels24 = Array.from({ length: 24 }, (_, i) => `2025-09-${String(i + 1).padStart(2, '0')}`);

        const customersOptions = {
            series: [
                { data: shuffle(dataset) }
            ],
            labels: labels24,
            chart: {
                type: "area",
                height: 50,
                sparkline: { enabled: true },
            },
            stroke: {
                curve: "smooth",
                width: 1.5
            },
            colors: ["var(--bs-primary)"],
            fill: {
                type: "gradient",
                gradient: {
                    shade: "light",
                    shadeIntensity: 0.8,
                    opacityFrom: 0.45,
                    opacityTo: 0.10,
                    stops: [0, 80, 100],
                    colorStops: [
                        [
                            { offset: 0,   color: "rgba(var(--bs-primary-rgb), 0.25)", opacity: 1 },
                            { offset: 60,  color: "rgba(var(--bs-primary-rgb), 0.12)", opacity: 1 },
                            { offset: 100, color: "rgba(var(--bs-primary-rgb), 0.05)", opacity: 0 }
                        ]
                    ]
                }
            },
            tooltip: {
                x: { show: false },
                marker: { show: false },
                y: {
                    title: { formatter: () => "" }
                }
            }
        };

        // Render
        new ApexCharts(document.querySelector("#total-customers"), customersOptions).render();

        /* Total Revenue */
        const revenueOptions = {
            series: [{
                data: shuffle(dataset)
            }],
            labels: Array.from({ length: 24 }, (_, i) => `2018-09-${String(i + 1).padStart(2, '0')}`),
            chart: {
                type: 'area',
                height: 50,
                sparkline: { enabled: true }
            },
            stroke: {
                curve: 'smooth',
                width: 1.5
            },
            colors: ["rgb(255, 90, 41)"],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100],
                    colorStops: [
                        [
                            { offset: 0,   color: "rgba(255, 90, 41, 0.25)", opacity: 1 },
                            { offset: 60,  color: "rgba(255, 90, 41, 0.10)", opacity: 1 },
                            { offset: 100, color: "rgba(255, 90, 41, 0.05)", opacity: 0 }
                        ]
                    ]
                }
            },
            tooltip: {
                x: { show: false },
                marker: { show: false },
                y: { title: { formatter: () => "" } }
            }
        };

        new ApexCharts(
            document.querySelector("#total-revenue"),
            revenueOptions
        ).render();

        /* Total Deals */
        const dealsOptions = {
            series: [{
                data: shuffle(dataset)
            }],
            labels: Array.from({ length: 24 }, (_, i) =>
                `2018-09-${String(i + 1).padStart(2, '0')}`
            ),
            chart: {
                type: 'area',
                height: 50,
                sparkline: { enabled: true }
            },
            stroke: {
                curve: 'smooth',
                width: 1.5
            },

            // Line color
            colors: ["rgb(0, 171, 85)"],

            // Gradient fill
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100],
                    colorStops: [
                        [
                            { offset: 0,   color: "rgba(0, 171, 85, 0.25)", opacity: 1 },
                            { offset: 60,  color: "rgba(0, 171, 85, 0.10)", opacity: 1 },
                            { offset: 100, color: "rgba(0, 171, 85, 0.05)", opacity: 0 }
                        ]
                    ]
                }
            },

            tooltip: {
                x: { show: false },
                marker: { show: false },
                y: { title: { formatter: () => "" } }
            }
        };

        new ApexCharts(
            document.querySelector("#total-deals"),
            dealsOptions
        ).render();

        /* Conversion Ratio */
        const conversionRatioOptions = {
            series: [{
                data: shuffle(dataset)
            }],
            labels: Array.from({ length: 24 }, (_, i) =>
                `2018-09-${String(i + 1).padStart(2, '0')}`
            ),
            chart: {
                type: 'area',
                height: 50,
                sparkline: { enabled: true }
            },
            stroke: {
                curve: 'smooth',
                width: 1.5
            },

            // Line Color
            colors: ["rgb(0, 123, 255)"],

            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100],
                    colorStops: [
                        [
                            { offset: 0,   color: "rgba(0, 123, 255, 0.25)", opacity: 1 },
                            { offset: 60,  color: "rgba(0, 123, 255, 0.10)", opacity: 1 },
                            { offset: 100, color: "rgba(0, 123, 255, 0.05)", opacity: 0 }
                        ]
                    ]
                }
            },

            tooltip: {
                x: { show: false },
                marker: { show: false },
                y: { title: { formatter: () => "" } }
            }
        };

        new ApexCharts(
            document.querySelector("#conversion-ratio"),
            conversionRatioOptions
        ).render();

        var options = {
            series: [{
                name: 'سود',
                type: 'column',
                data: [23, 11, 22, 27, 13, 22, 37, 21, 44, 22, 30]
            }, {
                name: 'درآمد',
                type: 'area',
                data: [44, 55, 41, 67, 22, 43, 21, 41, 56, 27, 43]
            }, {
                name: 'مخارج',
                type: 'line',
                data: [30, 25, 36, 30, 45, 35, 64, 52, 59, 36, 39]
            }],
            chart: {
                height: 320,
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
                    text: 'واحد',
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
                            return y.toFixed(0) + " واحد";
                        }
                        return y;

                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#revenue-analysis"), options);
        chart.render();

        var options = {
            series: [
                {
                    name: 'فروش تیم ۱',
                    data: [120, 80, 90, 70, 110, 60],  // Sample metrics
                },
                {
                    name: 'فروش تیم ۲',
                    data: [100, 95, 80, 90, 100, 75],
                },
                {
                    name: 'فروش تیم ۳',
                    data: [90, 70, 100, 80, 95, 85],
                }
            ],
            chart: {
                height: 350,
                type: 'radar',
                fontFamily: "Peyda",
                dropShadow: {
                    enabled: true,
                    blur: 2,
                    left: 2,
                    top: 2
                }
            },
            stroke: {
                width: 2
            },
            fill: {
                opacity: 0.2
            },
            markers: {
                size: 4
            },
            yaxis: {
                stepSize: 20
            },
            xaxis: {
                categories: ['لیدهای تماس‌گرفته‌شده', 'معاملات', 'پیگیری ها', 'جلسات', 'فرصت های جدید', 'تیکت های حل شده']
            }
        };

        var el3 = document.querySelector("#team-performance");
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