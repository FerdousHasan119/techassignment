<?php
session_start();
include '_dbconnect.php';

// Fetch all questions and their answers
$query = "SELECT q.id AS question_id, q.ques, a.id AS answer_id, a.answer, a.correct 
          FROM question q 
          JOIN answers a ON q.id = a.ques_id 
          ORDER BY q.id, a.id";
$result = mysqli_query($con, $query);

$questions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $questions[$row['question_id']]['question'] = $row['ques'];
    $questions[$row['question_id']]['answers'][] = [
        'answer_id' => $row['answer_id'],
        'answer_text' => $row['answer'],
        'is_correct' => $row['correct']
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Questions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .correct { color: green; font-weight: bold; }
        .actions { text-align: center; }
        .thumb-up { color: green; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mt-4">Manage Questions</h2>
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#insertModal">Add New Question</button>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Question</th>
                <th>Answers</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="questionTableBody">
            <?php foreach ($questions as $question_id => $data): ?>
                <tr id="question-<?php echo $question_id; ?>">
                    <td><?php echo $data['question']; ?></td>
                    <td>
                        <ul>
                            <?php foreach ($data['answers'] as $answer): ?>
                                <li>
                                    <?php echo $answer['answer_text']; ?>
                                    <?php if ($answer['is_correct']): ?>
                                        <span class="thumb-up">&#128077;</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    <td class="actions">
                        <button class="btn btn-warning btn-sm edit-question" data-question-id="<?php echo $question_id; ?>" data-toggle="modal" data-target="#editModal">Edit</button>
                        <button class="btn btn-danger btn-sm delete-question" data-question-id="<?php echo $question_id; ?>">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Insert Modal -->
<div class="modal fade" id="insertModal" tabindex="-1" role="dialog" aria-labelledby="insertModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="insertModalLabel">Add New Question</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="insertForm">
                    <div class="form-group">
                        <label for="new-question">Question</label>
                        <textarea class="form-control" id="new-question" name="question" required></textarea>
                    </div>
                    <div id="new-answers">
                        <div class="form-group">
                            <label>Answer</label>
                            <input type="text" class="form-control" name="answers[]" required><br>
                            <button type="button" class="remove-answer">Remove</button>
                            <input type="radio" name="correct" value="0"> Correct
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary add-answer">Add Answer</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Question</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="edit-question-id" name="question_id">
                    <div class="form-group">
                        <label for="edit-question">Question</label>
                        <textarea class="form-control" id="edit-question" name="question" required></textarea>
                    </div>
                    <div id="edit-answers"></div>
                    <button type="button" class="btn btn-secondary add-answer">Add Answer</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    // Add Answer
    $('.add-answer').on('click', function() {
        var newAnswerHtml = `
            <div class="form-group">
                <label>Answer</label>
                <input type="text" class="form-control" name="answers[]" required>
                <br>
                <button type="button" class="remove-answer">Remove</button>
                <input type="radio" name="correct" value="${$('#new-answers .form-group').length}"> Correct
            </div>`;
        $(this).prev().append(newAnswerHtml);
    });

    // Insert Form Submit
    $('#insertForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'insert_question.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#insertModal').modal('hide');
                $('#questionTableBody').append(response);
                $('#insertForm')[0].reset();
            }
        });
    });

    //remove
    $(document).on('click', '.remove-answer', function() {
            $(this).parent().remove();
    });

    // Edit Button Click
    $(document).on('click', '.edit-question', function() {
    var questionId = $(this).data('question-id');
    $.ajax({
        url: 'get_question.php',
        method: 'POST',
        data: { question_id: questionId },
        success: function(response) {
            var data = JSON.parse(response);
            $('#edit-question-id').val(data.question_id);
            $('#edit-question').val(data.question);
            $('#edit-answers').html('');
            $.each(data.answers, function(index, answer) {
                var checked = answer.correct ? 'checked' : ''; // Determine if the current answer is correct
                var answerHtml = `
                    <div class="form-group">
                        <input type="hidden" name="answer_ids[]" value="${answer.answer_id}">
                        <label>Answer</label>
                        <input type="text" class="form-control" name="answers[]" value="${answer.answer}" required>
                        <input type="radio" name="correct" value="${index}" ${checked}> Correct
                    </div>`;
                $('#edit-answers').append(answerHtml);
            });
            $('#editModal').modal('show');
        }
    });
});


    // Edit Form Submit
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'update_question.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#editModal').modal('hide');
                $('#question-' + $('#edit-question-id').val()).replaceWith(response);
            }
        });
    });

    // Delete Question
    $(document).on('click', '.delete-question', function() {
        if (!confirm('Are you sure you want to delete this question?')) return;
        var questionId = $(this).data('question-id');
        $.ajax({
            url: 'delete_question.php',
            method: 'POST',
            data: { question_id: questionId },
            success: function() {
                $('#question-' + questionId).remove();
            }
        });
    });
});
</script>
</body>
</html>
