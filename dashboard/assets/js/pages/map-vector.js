(function($) {
    'use strict';

    const map = new jsVectorMap({
                    selector: '#map',
                    map: 'world',
                })

            
    /* map with markers */
    var markers = [{
        name: 'Russia',
        coords: [61, 105],
        style: {
            initial: {
                fill: '#5c5cff'
            }
        }
    },
    {
        name: 'Greenland',
        coords: [72, -42],
        style: {
            initial: {
                fill: '#ff9251'
            }
        }
    },
    {
        name: 'Canada',
        coords: [56, -106],
        style: {
            initial: {
                fill: '#56de80'
            }
        }
    },
    {
        name: 'Palestine',
        coords: [31.5, 34.8],
        style: {
            initial: {
                fill: 'yellow'
            }
        }
    },
    {
        name: 'Brazil',
        coords: [-14.2350, -51.9253],
        style: {
            initial: {
                fill: '#000'
            }
        }
    },
    ];

    var test = new jsVectorMap({
        map: 'world',
        selector: '#marker-map',
        markersSelectable: true,
        onMarkerSelected(index, isSelected, selectedMarkers) {
            console.log(index, isSelected, selectedMarkers);
        },

        // -------- Labels --------
        labels: {
            markers: {
                render: function (marker) {
                    return marker.name
                },
            },
        },

        // -------- Marker and label style --------
        markers: markers,
        markerStyle: {
            hover: {
                stroke: "#DDD",
                strokeWidth: 3,
                fill: '#FFF'
            },
            selected: {
                fill: '#ff525d'
            }
        },
        markerLabelStyle: {
            initial: {
                fontFamily: 'Poppins',
                fontSize: 13,
                fontWeight: 500,
                fill: '#35373e',
            },
        },
    })

    /* maps with lines */
    var markers = [
        { name: 'Russia', coords: [61.5240, 105.3188] },
        { name: 'Egypt', coords: [26.8206, 30.8025] },
        { name: 'Greenland', coords: [71.7069, -42.6043], offsets: [2, 10] },
        { name: 'Canada', coords: [56, -106], offsets: [-7, 12] },
    ]

    var lines = [
        { from: 'Russia', to: 'Egypt', style: { stroke: '#abb0b7', strokeWidth: 1.5 } },
        { from: 'Canada', to: 'Russia', style: { stroke: '#abb0b7', strokeWidth: 1.5 } },
    ]

    new jsVectorMap({
        map: 'world',
        selector: document.querySelector('#lines-map'),
        // -------- Labels --------
        labels: {
            markers: {
                render: function (marker) {
                    return marker.name
                },
                offsets: function (index) {
                    return markers[index].offsets || [0, 0]
                }
            },
        },
        // -------- Marker and label style --------
        markers: markers,
        lines: lines,
        lineStyle: {
            animation: true,
            strokeDasharray: "6 3 6",
        },
        markerStyle: {
            initial: {
                r: 6,
                fill: 'rgb(115, 93, 255)',
                stroke: '#fff',
                strokeWidth: 3,
            }
        },
        markerLabelStyle: {
            initial: {
                fontSize: 13,
                fontWeight: 500,
                fill: '#35373e',
            },
        },
    })
}(jQuery) )