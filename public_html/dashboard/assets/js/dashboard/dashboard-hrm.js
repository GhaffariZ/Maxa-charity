(function($) {
    'use strict';
    function renderAllCharts() {
        if (typeof ApexCharts === 'undefined') return;

            var options = {
                series: [
                    {
                        name: 'کارمند جدید',
                        type: 'column',
                        data: [5, 8, 6, 10, 7, 12, 9]
                    },
                    {
                        name: 'کارمند اخراجی',
                        type: 'column',
                        data: [2, 1, 3, 2, 1, 2, 1]
                    },
                    {
                        name: 'رشد خالص',
                        type: 'line',
                        data: [3, 7, 3, 8, 6, 10, 8]
                    }
                ],
                chart: {
                    height: 330,
                    fontFamily: "Peyda",
                    type: 'line',
                    stacked: false,
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
                        columnWidth: '35%',
                        borderRadius: 5
                    }
                },
                fill: {
                    type: ['solid', 'solid', 'gradient'],
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.4,
                        inverseColors: false,
                        opacityFrom: 0.8,
                        opacityTo: 0.2,
                        stops: [0, 100]
                    }
                },
                colors: ['#3E7CFC', '#F84164', '#27C97D'],
                dataLabels: {
                    enabled: false
                },
                markers: {
                    size: 4
                },
                xaxis: {
                    categories: ['شنب', 'یکش', 'دوش', 'سه', 'چها', 'پنج', 'جمع'],
                    title: {
                        text: 'روزهای هفته',
                    }
                },
                yaxis: [
                    {
                        title: {
                            text: 'کارمند'
                        }
                    }
                ],
                legend: {
                    position: 'top'
                },
                tooltip: {
                    shared: true,
                    intersect: false
                }
            };

            var chart = new ApexCharts(document.querySelector("#hrm-dashboard"), options);
            chart.render();

            $('.dashboard-circle-progress').each(function() {
                var $this = $(this);
                var value = $this.data('value');          // percentage
                var color = $this.data('color') || 'primary';  // color name

                var options = {
                    series: [value, 100 - value],
                    chart: {
                        type: "donut",
                        width: 65,
                        height: 65,
                        sparkline: { enabled: true },
                    },
                    stroke: { width: 1 },
                    dataLabels: { enabled: false },
                    legend: { show: false },
                    plotOptions: {
                        pie: {
                            expandOnClick: false,
                            donut: {
                                size: "80%",
                                fontFamily: "Peyda",
                                background: "transparent",
                                labels: { show: false },
                            },
                        },
                    },
                    colors: [
                        `var(--bs-${color})`, 
                        `rgba(var(--bs-${color}-rgb), 0.15)`
                    ],
                    tooltip: { enabled: false }
                };

                var chart = new ApexCharts($this[0], options);
                chart.render();
            });

            var options = {
            series: [14, 23, 21, 17, 15],
            chart: {
                type: 'polarArea',
                height: 320,
            },
            labels: ['حضوری', 'دورکار', 'فریلنس', 'پارت تایم', 'داخلی'],
            stroke: {
                colors: ['#fff'],
            },
            fill: {
                opacity: 0.8,
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200,
                    },
                    legend: {
                        position: 'bottom',
                    },
                },
            }],
        };


        var el3 = document.querySelector("#team-work-chart");
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