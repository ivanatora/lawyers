<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <title>Lawyers and Customers</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

        <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/extjs/6.0.0/ext-all-debug.js"></script>

        <link rel="stylesheet" type="text/css" href="http://cdnjs.cloudflare.com/ajax/libs/extjs/6.0.0/classic/theme-triton/resources/theme-triton-all-debug.css">
        <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/extjs/6.0.0/classic/theme-triton/theme-triton-debug.js"></script>

        <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/extjs/6.0.0/packages/charts/classic/charts-debug.js"></script>
        <link type="text/css" href="http://cdnjs.cloudflare.com/ajax/libs/extjs/6.0.0/packages/charts/classic/classic/resources/charts-all-debug.css">


        <script type="text/javascript">
    Ext.application({
        name: 'cdnjs test with charts package',

        launch: function () {
            Ext.create('Ext.chart.PolarChart', {
                renderTo: document.body,
                width: 500,
                height: 500,
                store: {
                    fields: ['name', 'g1', 'g2'],
                    data: [
                        {"name": "Item-0", "g1": 18.34, "g2": 0.04},
                        {"name": "Item-1", "g1": 2.67, "g2": 14.87},
                        {"name": "Item-2", "g1": 1.90, "g2": 5.72},
                        {"name": "Item-3", "g1": 21.37, "g2": 2.13},
                        {"name": "Item-4", "g1": 2.67, "g2": 8.53},
                        {"name": "Item-5", "g1": 18.22, "g2": 4.62}
                    ]
                },

                interactions: ['rotate'],

                //configure the legend.
                legend: {
                    docked: 'bottom'
                },

                //describe the actual pie series.
                series: [{
                        type: 'pie',
                        xField: 'g1',
                        label: {
                            field: 'name',
                            display: 'rotate'
                        },
                        donut: 25,
                        style: {
                            miterLimit: 10,
                            lineCap: 'miter',
                            lineWidth: 2
                        }
                    }]
            });
        }
    });
        </script>
    </head>
    <body>

    </body>
</html>
