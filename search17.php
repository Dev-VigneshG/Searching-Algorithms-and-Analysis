<?php

// Initialize variables
$data = '';
$target = '';
$selectedAlgorithm = '';
$sortedArray = '';
$results = [];
$bestAlgorithm = '';
$bestTime = 0;
$resultMessage = '';

function measureTime($func, $array, $target) {
    $startTime = microtime(true);
    $result = $func($array, $target);
    $endTime = microtime(true);
    return [$result, ($endTime - $startTime) * 1000]; 
}

function linearSearch($array, $target) {
    foreach ($array as $index => $value) {
        if ($value == $target) {
            return $index;
        }
    }
    return -1;
}

function binarySearch($array, $target) {
    sort($array);
    $low = 0;
    $high = count($array) - 1;

    while ($low <= $high) {
        $mid = (int)(($low + $high) / 2);

        if ($array[$mid] == $target) {
            return $mid;
        } elseif ($array[$mid] < $target) {
            $low = $mid + 1;
        } else {
            $high = $mid - 1;
        }
    }
    return -1;
}

function jumpSearch($array, $target) {
    sort($array);
    $n = count($array);
    $step = (int)sqrt($n);
    $prev = 0;

    while ($array[min($step, $n) - 1] < $target) {
        $prev = $step;
        $step += (int)sqrt($n);
        if ($prev >= $n) return -1;
    }

    for ($i = $prev; $i < min($step, $n); $i++) {
        if ($array[$i] == $target) {
            return $i;
        }
    }
    return -1;
}

function interpolationSearch($array, $target) {
    sort($array);
    $low = 0;
    $high = count($array) - 1;

    while ($low <= $high && $target >= $array[$low] && $target <= $array[$high]) {
        if ($low == $high) {
            if ($array[$low] == $target) return $low;
            return -1;
        }

        $pos = $low + (int)((($target - $array[$low]) / ($array[$high] - $array[$low])) * ($high - $low));

        if ($array[$pos] == $target) {
            return $pos;
        } elseif ($array[$pos] < $target) {
            $low = $pos + 1;
        } else {
            $high = $pos - 1;
        }
    }
    return -1;
}

function exponentialSearch($array, $target) {
    sort($array);
    $n = count($array);
    if ($n == 0) return -1; // Check if the array is empty

    if ($array[0] == $target) return 0;

    $index = 1;
    while ($index < $n && $array[$index] <= $target) {
        $index *= 2;
    }

    $low = (int)($index / 2);
    $high = min($index, $n - 1);

    if ($high >= $low) {
        return binarySearch(array_slice($array, $low, $high - $low + 1), $target);
    } else {
        return -1;
    }
}

function ternarySearch($array, $target) {
    sort($array);
    $low = 0;
    $high = count($array) - 1;

    while ($low <= $high) {
        $mid1 = $low + (int)(($high - $low) / 3);
        $mid2 = $high - (int)(($high - $low) / 3);

        if ($array[$mid1] == $target) {
            return $mid1;
        } elseif ($array[$mid2] == $target) {
            return $mid2;
        } elseif ($target < $array[$mid1]) {
            $high = $mid1 - 1;
        } elseif ($target > $array[$mid2]) {
            $low = $mid2 + 1;
        } else {
            $low = $mid1 + 1;
            $high = $mid2 - 1;
        }
    }
    return -1;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = isset($_POST['data']) ? $_POST['data'] : '';
    $target = isset($_POST['target']) ? trim($_POST['target']) : '';
    $selectedAlgorithm = isset($_POST['algorithm']) ? $_POST['algorithm'] : '';

    if (empty($data) || empty($target) || empty($selectedAlgorithm)) {
        $sortedArray = "";
        $results = [];
        $bestAlgorithm = '';
        $bestTime = 0;
        $resultMessage = "Error: Please provide valid input data, a target value, and select an algorithm.";
    } else {
        $array = array_map('trim', explode(',', $data));
        $array = array_map('intval', $array);
        sort($array);

        if (!is_numeric($target)) {
            $resultMessage = "Error: Target value must be numeric.";
            $sortedArray = "";
            $results = [];
            $bestAlgorithm = '';
            $bestTime = 0;
        } else {
            $target = (int)$target;

            $results = [];
            $results['linear'] = measureTime('linearSearch', $array, $target);
            $results['binary'] = measureTime('binarySearch', $array, $target);
            $results['jump'] = measureTime('jumpSearch', $array, $target);
            $results['interpolation'] = measureTime('interpolationSearch', $array, $target);
            $results['exponential'] = measureTime('exponentialSearch', $array, $target);
            $results['ternary'] = measureTime('ternarySearch', $array, $target);

            $minTime = min(array_column($results, 1));
            $bestAlgorithm = array_search($minTime, array_column($results, 1));
            $bestTime = $minTime;

            $sortedArray = implode(', ', $array);

            $resultMessage = $results[$selectedAlgorithm][0] == -1 ? "Element not found" : "Element found at index " . $results[$selectedAlgorithm][0];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Algorithm Selector</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            color: #4CAF50;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
        }
        input[type="radio"] {
            margin-right: 10px;
        }
        fieldset {
            border: 2px solid #4CAF50;
            border-radius: 8px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        legend {
            font-size: 1.2em;
            color: #4CAF50;
        }
        button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        button:hover {
            background-color: #45a049;
        }
        p {
            font-size: 1.1em;
            text-align: center;
        }
        .chart-container {
            width: 100%;
            height: 400px;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: #fff;
        }
        .best-algorithm {
            color: #d9534f;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Searching Algorithms and Analysis</h1>
        <form method="POST" action="">
            <label for="data">Enter the array (comma separated):</label>
            <input type="text" id="data" name="data" value="<?php echo htmlspecialchars($data); ?>">

            <label for="target">Enter the target value:</label>
            <input type="text" id="target" name="target" value="<?php echo htmlspecialchars($target); ?>">

            <fieldset>
                <legend>Select an Algorithm</legend>
                <input type="radio" id="linear" name="algorithm" value="linear" <?php echo ($selectedAlgorithm == 'linear') ? 'checked' : ''; ?>>
                <label for="linear">Linear Search</label><br>
                <input type="radio" id="binary" name="algorithm" value="binary" <?php echo ($selectedAlgorithm == 'binary') ? 'checked' : ''; ?>>
                <label for="binary">Binary Search</label><br>
                <input type="radio" id="jump" name="algorithm" value="jump" <?php echo ($selectedAlgorithm == 'jump') ? 'checked' : ''; ?>>
                <label for="jump">Jump Search</label><br>
                <input type="radio" id="interpolation" name="algorithm" value="interpolation" <?php echo ($selectedAlgorithm == 'interpolation') ? 'checked' : ''; ?>>
                <label for="interpolation">Interpolation Search</label><br>
                <input type="radio" id="exponential" name="algorithm" value="exponential" <?php echo ($selectedAlgorithm == 'exponential') ? 'checked' : ''; ?>>
                <label for="exponential">Exponential Search</label><br>
                <input type="radio" id="ternary" name="algorithm" value="ternary" <?php echo ($selectedAlgorithm == 'ternary') ? 'checked' : ''; ?>>
                <label for="ternary">Ternary Search</label><br>
            </fieldset>

            <button type="submit">Submit</button>
        </form>

        <p><?php echo $resultMessage; ?></p>

        <?php if ($sortedArray): ?>
        <h2>Sorted Array</h2>
        <p><?php echo $sortedArray; ?></p>

        <h2>Results</h2>
        <table>
            <thead>
                <tr>
                    <th>Algorithm</th>
                    <th>Time (ms)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $algorithm => $result): ?>
                    <tr>
                        <td><?php echo ucfirst($algorithm); ?></td>
                        <td><?php echo number_format($result[1], 4); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Performance Chart</h2>
        <div class="chart-container">
            <canvas id="performanceChart"></canvas>
        </div>

        <script>
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const performanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_map('ucfirst', array_keys($results))); ?>,
                    datasets: [{
                        label: 'Time (ms)',
                        data: <?php echo json_encode(array_column($results, 1)); ?>,
                        backgroundColor: '#4CAF50',
                        borderColor: '#4CAF50',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
        <?php endif; ?>
    </div>
</body>
</html>
