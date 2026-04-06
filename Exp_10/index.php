<?php
session_start();

if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = [];
}

$displayValue = "0";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['clear_history'])) {
        $_SESSION['history'] = [];
    } elseif (isset($_POST['calculate'])) {
        $expression = $_POST['expression'];
        
        // Sanitize the expression: allow only numbers, dot, and basic math operators
        $safe_expression = preg_replace('/[^0-9\.\+\-\*\/]/', '', $expression);
        
        if ($safe_expression !== '') {
            try {
                // Safely evaluate the math string
                $result = @eval("return ($safe_expression);");
                if ($result === false || $result === null) {
                    $displayValue = "Error";
                } else {
                    $displayValue = escapeshellcmd($result); // Ensuring it's a string/number
                    // Add to history
                    $_SESSION['history'][] = "$safe_expression = $displayValue";
                }
            } catch (Throwable $t) {
                // Catch any divide by zero or parsing errors
                $displayValue = "Error";
            }
        } else {
            $displayValue = "Error";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Calculator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #80807c;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial;
        }
        .container {
            display: flex;
            gap: 20px;
        }
        .calculator {
            width: 300px;
            background: #000;
            padding: 20px;
            border-radius: 20px;
        }
        .history {
            width: 300px;
            background: #222;
            padding: 20px;
            border-radius: 20px;
            color: white;
            display: flex;
            flex-direction: column;
            max-height: 480px;
        }
        .history h3 {
            margin-bottom: 15px;
            text-align: center;
            color: #1ece6a;
        }
        .history-list {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 15px;
        }
        .history ul {
            list-style-type: none;
        }
        .history li {
            padding: 8px 0;
            border-bottom: 1px solid #444;
            font-size: 18px;
        }
        #display {
            width: 100%;
            height: 80px;
            background: #ecec0c;
            color: rgb(19, 19, 19);
            font-size: 40px;
            text-align: right;
            padding: 10px;
            border: none;
            margin-bottom: 15px;
            border-radius: 10px;
        }
        .buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        button[type="button"], button[type="submit"] {
            height: 65px;
            font-size: 24px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            background: #333;
            color: white;
            transition: opacity 0.2s;
        }
        button:hover {
            opacity: 0.8;
        }
        .operator {
            background: #1ece6a !important;
        }
        .equals {
            background: #810b58d1 !important;
        }
        .clear {
            background: #ea0d0d !important;
            color: #000 !important;
        }
        .zero {
            grid-column: span 2;
        }
        .clear-hist-btn {
            width: 100%;
            background: #ea0d0d !important;
            height: 40px !important;
            font-size: 16px !important;
            border-radius: 10px !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Calculator Form -->
        <form method="POST" class="calculator">
            <input type="text" id="display" name="expression" readonly value="<?php echo htmlspecialchars($displayValue); ?>">
            
            <div class="buttons">
                <!-- Buttons are type="button" so they don't submit the form, except "=" -->
                <button type="button" class="clear" onclick="clearDisplay()"> C </button>
                <button type="button" class="operator" onclick="appendOperator('+')"> + </button>
                <button type="button" class="operator" onclick="appendOperator('-')"> - </button>
                <button type="button" class="operator" onclick="appendOperator('*')"> × </button>
               
                <button type="button" onclick="appendNumber('1')"> 1 </button>
                <button type="button" onclick="appendNumber('2')"> 2 </button>
                <button type="button" onclick="appendNumber('3')"> 3 </button>
                <button type="button" class="operator" onclick="appendOperator('/')"> ÷ </button>
                
                <button type="button" onclick="appendNumber('4')"> 4 </button>
                <button type="button" onclick="appendNumber('5')"> 5 </button>
                <button type="button" onclick="appendNumber('6')"> 6 </button>
                <button type="submit" name="calculate" class="equals"> = </button>
                
                <button type="button" onclick="appendNumber('7')"> 7 </button>
                <button type="button" onclick="appendNumber('8')"> 8 </button>
                <button type="button" onclick="appendNumber('9')"> 9 </button>
                <button type="button" class="clear" onclick="deleteLast()"> ⌫ </button>
                
                <button type="button" class="zero" onclick="appendNumber('0')"> 0 </button>
                <button type="button" onclick="appendNumber('.')"> . </button>
            </div>
        </form>

        <!-- History Display -->
        <div class="history">
            <h3>Calculation History</h3>
            <div class="history-list">
                <ul>
                    <?php if (empty($_SESSION['history'])): ?>
                        <li style="text-align: center; color: #888; font-size: 16px;">No history yet.</li>
                    <?php else: ?>
                        <?php foreach (array_reverse($_SESSION['history']) as $entry): ?>
                            <li><?php echo htmlspecialchars($entry); ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            
            <form method="POST">
                <button type="submit" name="clear_history" class="clear-hist-btn">Clear History</button>
            </form>
        </div>
    </div>

    <script>
        let display = document.getElementById('display');
        
        function appendNumber(num) {
            if(display.value === '0' || display.value === 'Error') {
                display.value = num;
            } else {
                display.value += num;
            }
        }
        
        function appendOperator(op) {
            if (display.value === 'Error') {
                display.value = '0';
            }
            let lastChar = display.value[display.value.length - 1];
            if('+-*/'.includes(lastChar)) {
                display.value = display.value.slice(0, -1) + op;
            } else {
                display.value += op;
            }
        }
        
        function clearDisplay() {
            display.value = '0';
        }
        
        function deleteLast() {
            if (display.value === 'Error') {
                display.value = '0';
                return;
            }
            display.value = display.value.slice(0, -1);
            if(display.value === '') {
                display.value = '0';
            }
        }
    </script>
</body>
</html>
