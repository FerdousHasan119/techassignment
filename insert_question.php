<?php
include '_dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question = mysqli_real_escape_string($con, $_POST['question']);
    $answers = $_POST['answers'];
    $correct = (int)$_POST['correct'];

    // Insert the question
    $insert_question_query = "INSERT INTO question (ques) VALUES ('$question')";
    mysqli_query($con, $insert_question_query);
    $question_id = mysqli_insert_id($con);

    // Insert the answers
    foreach ($answers as $index => $answer_text) {
        $is_correct = ($index == $correct) ? 1 : 0;
        $answer_text = mysqli_real_escape_string($con, $answer_text);
        $insert_answer_query = "INSERT INTO answers (ques_id, answer, correct) VALUES ($question_id, '$answer_text', $is_correct)";
        mysqli_query($con, $insert_answer_query);
    }

    // Fetch the new question with answers to append to the table
    // $query = "SELECT q.id AS question_id, q.ques, a.id AS answer_id, a.answer, a.correct 
    //           FROM question q 
    //           JOIN answers a ON q.id = a.ques_id
    //           WHERE a.ques_id= $question_id 
    //           ORDER BY a.id";
    // $result = mysqli_query($con, $query);

    // $question = mysqli_fetch_assoc($result);
    // $answers = [];
    // while ($row = mysqli_fetch_assoc($result)) {
    //     $answers[] = $row;
    // }
    $query = "SELECT q.id AS question_id, q.ques, a.id AS answer_id, a.answer, a.correct 
    FROM question q 
    JOIN answers a ON q.id = a.ques_id
    WHERE a.ques_id = $question_id 
    ORDER BY a.id";
    $result = mysqli_query($con, $query);

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
}
?>
