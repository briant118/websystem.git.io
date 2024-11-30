<?php
session_start();

// Initialize session variables
if (!isset($_SESSION['score'])) {
    $_SESSION['score'] = ['right' => 0, 'wrong' => 0];
}
if (!isset($_SESSION['quiz_settings'])) {
    $_SESSION['quiz_settings'] = [
        'operator' => 'multiply', // Default operator is multiply
        'num_items' => 4, // 4 choices for each question
        'max_item' => 2, // Max number of questions (max_item)
        'level' => '1-10',
        'custom_level_start' => 1,
        'custom_level_end' => 10,
        'answer_range' => 5 // Default answer range tolerance
    ];
}
if (!isset($_SESSION['quiz_started'])) {
    $_SESSION['quiz_started'] = false;
}
if (!isset($_SESSION['show_settings'])) {
    $_SESSION['show_settings'] = false;
}
if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0; // Track current question
}

// Reset the quiz
if (isset($_POST['close_quiz'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Start Quiz
if (isset($_POST['start_quiz'])) {
    $_SESSION['quiz_started'] = true;
    $_SESSION['score'] = ['right' => 0, 'wrong' => 0];
    $_SESSION['current_question'] = 1; // Start from the first question
}

// Handle Settings Button Toggle
if (isset($_POST['toggle_settings'])) {
    $_SESSION['show_settings'] = !$_SESSION['show_settings'];
}

// Handle Settings Update
if (isset($_POST['update_settings'])) {
    $_SESSION['quiz_settings']['operator'] = $_POST['operator']; // Set the selected operator
    $_SESSION['quiz_settings']['max_item'] = (int)$_POST['max_item']; // Max number of questions
    $_SESSION['quiz_settings']['level'] = $_POST['level'];
    $_SESSION['quiz_settings']['answer_range'] = (int)$_POST['answer_range']; // Store the answer range

    if ($_POST['level'] === 'custom') {
        $_SESSION['quiz_settings']['custom_level_start'] = (int)$_POST['custom_level_start'];
        $_SESSION['quiz_settings']['custom_level_end'] = (int)$_POST['custom_level_end'];
    }

    $_SESSION['show_settings'] = false;
}

// Handle answer submission
if ($_SESSION['quiz_started'] && isset($_POST['answer'])) {
    $num1 = $_POST['num1'];
    $num2 = $_POST['num2'];
    $operator = $_SESSION['quiz_settings']['operator'];
    
    switch ($operator) {
        case 'add':
            $correct_answer = $num1 + $num2;
            break;
        case 'subtract':
            $correct_answer = $num1 - $num2;
            break;
        case 'multiply':
            $correct_answer = $num1 * $num2;
            break;
    }

    if ((int)$_POST['answer'] === $correct_answer) {
        $_SESSION['score']['right']++;
        $message = "Correct!";
    } else {
        $_SESSION['score']['wrong']++;
        $message = "Wrong! Correct answer was $correct_answer.";
    }

    // Move to next question
    if ($_SESSION['current_question'] < $_SESSION['quiz_settings']['max_item']) {
        $_SESSION['current_question']++;
    } else {
        $message = "Quiz completed! Final score: Correct: {$_SESSION['score']['right']}, Wrong: {$_SESSION['score']['wrong']}";
    }
}

// Only generate random question once the quiz starts
if ($_SESSION['quiz_started'] && $_SESSION['current_question'] <= $_SESSION['quiz_settings']['max_item']) {
    $range = [1, 10];  // Default range for 1-10

    // Handle custom level
    if ($_SESSION['quiz_settings']['level'] === '1-10') {
        $range = [1, 10];
    } elseif ($_SESSION['quiz_settings']['level'] === '11-100') {
        $range = [11, 100];
    } elseif ($_SESSION['quiz_settings']['level'] === 'custom') {
        $range = [$_SESSION['quiz_settings']['custom_level_start'], $_SESSION['quiz_settings']['custom_level_end']];
    }

    $num1 = rand($range[0], $range[1]);
    $num2 = rand($range[0], $range[1]);

    // Operator symbol based on the selected operator
    $operator_symbol = '';
    switch ($_SESSION['quiz_settings']['operator']) {
        case 'add':
            $operator_symbol = '+';
            break;
        case 'subtract':
            $operator_symbol = '-';
            break;
        case 'multiply':
            $operator_symbol = '×';
            break;
    }

    $correct_answer = 0;
    switch ($_SESSION['quiz_settings']['operator']) {
        case 'add':
            $correct_answer = $num1 + $num2;
            break;
        case 'subtract':
            $correct_answer = $num1 - $num2;
            break;
        case 'multiply':
            $correct_answer = $num1 * $num2;
            break;
    }

    // Get the answer range from session settings
    $range_offset = isset($_SESSION['quiz_settings']['answer_range']) ? $_SESSION['quiz_settings']['answer_range'] : 5; // Default to 5 if not set

    // Create choices with a range around the correct answer
    $choices = [$correct_answer];

    // Generate random choices within the range of the correct answer
    while (count($choices) < 4) { // Always 4 choices
        $choice = rand($correct_answer - $range_offset, $correct_answer + $range_offset);
        
        // Ensure the choice is not already in the choices array
        if (!in_array($choice, $choices)) {
            $choices[] = $choice;
        }
    }

    // Shuffle the choices to randomize their order
    shuffle($choices);
} else {
    // Set default values for before quiz starts
    $num1 = 0;
    $num2 = 0;
    $operator_symbol = '+';
    $correct_answer = 0;
    $choices = [0, 1, 2, 3];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Mathematics</title>
    <style>
        .container {
            width: 300px;
            margin: auto;
            text-align: center;
            border: 1px solid #ccc;
            padding: 20px;
        }
        .button-container button {
            width: 100px;
            margin: 5px;
        }
        .score {
            display: flex;
            justify-content: space-around;
        }
        .settings {
            margin-top: 20px;
        }
        .result {
            font-size: 1.2em;
            margin-top: 10px;
            color: green;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Simple Mathematics</h1>
    <div>
        <?php if ($_SESSION['quiz_started']): ?>
            <h2>
                <?php
                // Display the question in the format "0 + 0 = ?"
                echo "$num1 $operator_symbol $num2 = ?";
                ?>
            </h2>
            <form method="post">
                <input type="hidden" name="num1" value="<?= $num1 ?>">
                <input type="hidden" name="num2" value="<?= $num2 ?>">
                <?php foreach ($choices as $choice) { ?>
                    <button type="submit" name="answer" value="<?= $choice ?>"><?= $choice ?></button>
                <?php } ?>
            </form>

            <?php if (isset($message)): ?>
                <div class="result"><?= $message ?></div>
            <?php endif; ?>
        <?php else: ?>
            <p>Quiz hasn't started yet. Click "Start Quiz" to begin.</p>
        <?php endif; ?>
    </div>

    <div class="score">
        <p>Correct: <?= $_SESSION['score']['right'] ?></p>
        <p>Wrong: <?= $_SESSION['score']['wrong'] ?></p>
    </div>

    <div class="button-container">
        <form method="post">
            <button type="submit" name="start_quiz">Start Quiz</button>
            <button type="submit" name="close_quiz">Close</button>
            <button type="submit" name="toggle_settings">
                <?= $_SESSION['show_settings'] ? 'Close Settings' : 'Settings >>' ?>
            </button>
        </form>
    </div>

    <?php if ($_SESSION['show_settings']): ?>
        <!-- Update Settings Form -->
        <div class="settings">
            <h3>Settings</h3>
            <form method="post">
                <label>Level:</label><br>
                <input type="radio" name="level" value="1-10" <?= $_SESSION['quiz_settings']['level'] === '1-10' ? 'checked' : '' ?>> 1-10<br>
                <input type="radio" name="level" value="11-100" <?= $_SESSION['quiz_settings']['level'] === '11-100' ? 'checked' : '' ?>> 11-100<br>
                <input type="radio" name="level" value="custom" <?= $_SESSION['quiz_settings']['level'] === 'custom' ? 'checked' : '' ?>> Custom Range<br><br>

                <?php if ($_SESSION['quiz_settings']['level'] === 'custom'): ?>
                    <label>Start: <input type="number" name="custom_level_start" value="<?= $_SESSION['quiz_settings']['custom_level_start'] ?>"></label><br>
                    <label>End: <input type="number" name="custom_level_end" value="<?= $_SESSION['quiz_settings']['custom_level_end'] ?>"></label><br>
                <?php endif; ?>

                <label>Max Questions:</label><br>
                <input type="number" name="max_item" value="<?= $_SESSION['quiz_settings']['max_item'] ?>"><br><br>

                <label>Answer Range:</label><br>
                <input type="number" name="answer_range" value="<?= $_SESSION['quiz_settings']['answer_range'] ?>" min="1"><br><br>

                <label>Operator:</label><br>
                <input type="radio" name="operator" value="add" <?= $_SESSION['quiz_settings']['operator'] === 'add' ? 'checked' : '' ?>> Addition (+)<br>
                <input type="radio" name="operator" value="subtract" <?= $_SESSION['quiz_settings']['operator'] === 'subtract' ? 'checked' : '' ?>> Subtraction (-)<br>
                <input type="radio" name="operator" value="multiply" <?= $_SESSION['quiz_settings']['operator'] === 'multiply' ? 'checked' : '' ?>> Multiplication (×)<br><br>

                <button type="submit" name="update_settings">Update Settings</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
