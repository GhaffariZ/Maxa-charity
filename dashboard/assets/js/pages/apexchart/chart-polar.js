(function($) {
    'use strict';
    $(document).ready(function() {
        if (typeof ApexCharts === 'undefined') return;

            /* basic polar area chart */
            var options = {
                series: [14, 23, 21, 17, 15],
                chart: {
                    type: 'polarArea',
                    height: 300
                },
                labels: ["سری ۱", "سری ۲", "سری ۳", "سری ۴", "سری ۵"],
                stroke: {
                    colors: ['#fff']
                },
                fill: {
                    opacity: 0.8
                },
                legend: {
                    position: 'bottom',
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
                }]
            };
            var chart = new ApexCharts(document.querySelector("#polararea-basic"), options);
            chart.render();

            /* polar area monochrome chart */
            var options = {
                series: [42, 47, 52, 58, 65],
                chart: {
                    height: 300,
                    type: 'polarArea'
                },
                labels: ['رز ۱', 'رز ۲', 'رز ۳', 'رز ۴', 'رز ۵'],
                fill: {
                    opacity: 1
                },
                stroke: {
                    width: 1,
                    colors: undefined
                },
                yaxis: {
                    show: false
                },
                legend: {
                    position: 'bottom',
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
                plotOptions: {
                    polarArea: {
                        rings: {
                            strokeWidth: 0
                        },
                        spokes: {
                            strokeWidth: 0
                        },
                    }
                },
                theme: {
                    monochrome: {
                        enabled: true,
                        shadeTo: 'light',
                        shadeIntensity: 0.6,
                        color: "#735dff",
                    }
                }
            };
            var chart = new ApexCharts(document.querySelector("#polararea-monochrome"), options);
                chart.render();
                setTimeout(function() {
                    window.dispatchEvent(new Event('resize'));
                }, 500);
    });
})(jQuery);