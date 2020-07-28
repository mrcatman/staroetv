
    <div class="chart" style="position: relative;width:900px;height:600px; background: #fff">
        <canvas id="myChart" width="400" height="400"></canvas>

    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js"></script>
    <script>
        var ctx = document.getElementById('myChart');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!!  $data->pluck('month') !!},
                datasets: [{
                    label: 'Сообщения',
                    data: {{json_encode($data->pluck('count'))}},
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    </script>
