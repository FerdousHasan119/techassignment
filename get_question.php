<?php
include '_dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_id = (int)$_POST['question_id'];

    $query = "SELECT q.id AS question_id, q.ques, a.id AS answer_id, a.answer, a.correct 
              FROM question q 
              JOIN answers a ON q.id = a.ques_id 
              WHERE q.id = $question_id 
              ORDER BY a.id";
    $result = mysqli_query($con, $query);

    $question = null;
    $answers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        if (!$question) {
            $question = $row;
        }
        $answers[] = $row;
    }

    

    echo json_encode(['question_id' => $question_id, 'question' => $question['ques'], 'answers' => $answers]);
}
?>
