<?php
include '_dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_id = (int)$_POST['question_id'];
    $question = mysqli_real_escape_string($con, $_POST['question']);
    $answers = $_POST['answers'];
    $answer_ids = $_POST['answer_ids'];
    $correct = (int)$_POST['correct'];

    // Update the question
    $update_question_query = "UPDATE question SET ques = '$question' WHERE id = $question_id";
    mysqli_query($con, $update_question_query);

    // Update the answers
    // foreach ($answers as $index => $answer_text) {
    //     $answer_id = (int)$answer_ids[$index];
    //     $is_correct = ($index == $correct) ? 1 : 0;
    //     $answer_text = mysqli_real_escape_string($con, $answer_text);
    //     $update_answer_query = "UPDATE answers SET answer = '$answer_text', correct = $is_correct WHERE id = $answer_id";
    //     mysqli_query($con, $update_answer_query);
    // }
    foreach ($answers as $index => $answer_text) {
        $answer_id = (int)$answer_ids[$index];
        $is_correct = ($index == $correct) ? 1 : 0;
        $answer_text = mysqli_real_escape_string($con, $answer_text);
        
        if ($answer_id > 0) {
            // Existing answer, update it
            $update_answer_query = "UPDATE answers SET answer = '$answer_text', correct = $is_correct WHERE id = $answer_id";
            mysqli_query($con, $update_answer_query);
        } else {
            // New answer, insert it
            $insert_answer_query = "INSERT INTO answers (ques_id, answer, correct) VALUES ($question_id, '$answer_text', $is_correct)";
            mysqli_query($con, $insert_answer_query);
        }
    }

    // Fetch the updated question with answers to replace in the table
    $query = "SELECT q.id AS question_id, q.ques, a.id AS answer_id, a.answer, a.correct 
              FROM question q 
              JOIN answers a ON q.id = a.ques_id 
              WHERE q.id = $question_id 
              ORDER BY a.id";
    $result = mysqli_query($con, $query);

    if ($result) {
        $answers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $question = $row;
            $answers[] = $row;
        }

        echo '<tr id="question-' . $question_id . '">
                <td>' . $question['ques'] . '</td>
                <td><ul>';
        foreach ($answers as $answer) {
            echo '<li>' . $answer['answer'];
            if ($answer['correct']) {
                echo ' <span class="thumb-up">&#128077;</span>';
            }
            echo '</li>';
        }
        echo '  </ul></td>
                <td class="actions">
                    <button class="btn btn-warning btn-sm edit-question" data-question-id="' . $question_id . '" data-toggle="modal" data-target="#editModal">Edit</button>
                    <button class="btn btn-danger btn-sm delete-question" data-question-id="' . $question_id . '">Delete</button>
                </td>
              </tr>';
    } else {
        echo "No rows returned.";
    }
}
?>
