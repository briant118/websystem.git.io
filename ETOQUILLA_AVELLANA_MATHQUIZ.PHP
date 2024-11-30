<?php
session_start();

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Mathematics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Additional custom styles if necessary */
        .container {
            max-width: 600px;
            margin-top: 20px;
        }
        .score {
            display: flex;
            justify-content: space-around;
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
    <h1 class="text-center mb-4">Simple Mathematics</h1>

    <div class="mb-3">
        <?php if ($_SESSION['quiz_started']): ?>
            <h2 class="text-center">
                <?php
                // Display the question in the format "0 + 0 = ?"
                echo "$num1 $operator_symbol $num2 = ?";
                ?>
            </h2>
            <form method="post" class="text-center">
                <input type="hidden" name="num1" value="<?= $num1 ?>">
                <input type="hidden" name="num2" value="<?= $num2 ?>">
                <?php foreach ($choices as $choice) { ?>
                    <button type="submit" name="answer" value="<?= $choice ?>" class="btn btn-primary m-2"><?= $choice ?></button>
                <?php } ?>
            </form>

            <?php if (isset($message)): ?>
                <div class="result text-center"><?= $message ?></div>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-center">Quiz hasn't started yet. Click "Start Quiz" to begin.</p>
        <?php endif; ?>
    </div>

    <div class="score d-flex justify-content-around mb-4">
        <p>Correct: <?= $_SESSION['score']['right'] ?></p>
        <p>Wrong: <?= $_SESSION['score']['wrong'] ?></p>
    </div>

    <div class="d-flex justify-content-center mb-4">
        <form method="post">
            <button type="submit" name="start_quiz" class="btn btn-success m-2">Start Quiz</button>
            <button type="submit" name="close_quiz" class="btn btn-danger m-2">Close</button>
            <button type="submit" name="toggle_settings" class="btn btn-secondary m-2">
                <?= $_SESSION['show_settings'] ? 'Close Settings' : 'Settings >>' ?>
            </button>
        </form>
    </div>

    <?php if ($_SESSION['show_settings']): ?>
        <!-- Update Settings Form -->
        <div class="settings mb-4">
            <h3>Settings</h3>
            <form method="post">
                <div class="mb-3">
                    <label>Level:</label><br>
                    <input type="radio" name="level" value="1-10" <?= $_SESSION['quiz_settings']['level'] === '1-10' ? 'checked' : '' ?>> 1-10<br>
                    <input type="radio" name="level" value="11-100" <?= $_SESSION['quiz_settings']['level'] === '11-100' ? 'checked' : '' ?>> 11-100<br>
                    <input type="radio" name="level" value="custom" <?= $_SESSION['quiz_settings']['level'] === 'custom' ? 'checked' : '' ?>> Custom Range<br>
                </div>

                <?php if ($_SESSION['quiz_settings']['level'] === 'custom'): ?>
                    <div class="mb-3">
                        <label>Start: <input type="number" name="custom_level_start" value="<?= $_SESSION['quiz_settings']['custom_level_start'] ?>" class="form-control"></label><br>
                        <label>End: <input type="number" name="custom_level_end" value="<?= $_SESSION['quiz_settings']['custom_level_end'] ?>" class="form-control"></label><br>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label>Max Questions:</label><br>
                    <input type="number" name="max_item" value="<?= $_SESSION['quiz_settings']['max_item'] ?>" class="form-control"><br>
                </div>

                <div class="mb-3">
                    <label>Answer Range:</label><br>
                    <input type="number" name="answer_range" value="<?= $_SESSION['quiz_settings']['answer_range'] ?>" min="1" class="form-control"><br>
                </div>

                <div class="mb-3">
                    <label>Operator:</label><br>
                    <input type="radio" name="operator" value="add" <?= $_SESSION['quiz_settings']['operator'] === 'add' ? 'checked' : '' ?>> Addition (+)<br>
                    <input type="radio" name="operator" value="subtract" <?= $_SESSION['quiz_settings']['operator'] === 'subtract' ? 'checked' : '' ?>> Subtraction (-)<br>
                    <input type="radio" name="operator" value="multiply" <?= $_SESSION['quiz_settings']['operator'] === 'multiply' ? 'checked' : '' ?>> Multiplication (×)<br>
                </div>

                <div class="text-center">
                    <button type="submit" name="update_settings" class="btn btn-primary">Update Settings</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
