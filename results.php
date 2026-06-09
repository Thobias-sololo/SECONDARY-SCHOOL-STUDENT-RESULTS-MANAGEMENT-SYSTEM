<?php

function grade_for_mark(PDO $pdo, float $mark): array
{
    $stmt = $pdo->prepare('SELECT * FROM grades WHERE ? BETWEEN min_mark AND max_mark LIMIT 1');
    $stmt->execute([$mark]);
    return $stmt->fetch() ?: ['grade' => '-', 'remark' => 'No grade'];
}

function student_average(PDO $pdo, int $studentId, int $termId, int $yearId, bool $approvedOnly = true): float
{
    $statusSql = $approvedOnly ? "AND status = 'approved'" : '';
    $stmt = $pdo->prepare("SELECT AVG(marks) AS avg_marks FROM results WHERE student_id = ? AND term_id = ? AND academic_year_id = ? {$statusSql}");
    $stmt->execute([$studentId, $termId, $yearId]);
    return round((float) ($stmt->fetch()['avg_marks'] ?? 0), 2);
}

function class_rankings(PDO $pdo, int $formId, int $streamId, int $termId, int $yearId, bool $approvedOnly = true): array
{
    $statusSql = $approvedOnly ? "AND r.status = 'approved'" : '';
    $stmt = $pdo->prepare("
        SELECT s.id, s.admission_no, CONCAT(s.first_name, ' ', s.last_name) AS student_name, AVG(r.marks) AS average_marks
        FROM students s
        LEFT JOIN results r ON r.student_id = s.id AND r.term_id = ? AND r.academic_year_id = ? {$statusSql}
        WHERE s.form_id = ? AND s.stream_id = ? AND s.status = 'active'
        GROUP BY s.id
        ORDER BY average_marks DESC, student_name ASC
    ");
    $stmt->execute([$termId, $yearId, $formId, $streamId]);
    $rows = $stmt->fetchAll();
    $position = 1;
    foreach ($rows as &$row) {
        $row['average_marks'] = round((float) $row['average_marks'], 2);
        $row['position'] = $position++;
    }
    return $rows;
}

function student_position(PDO $pdo, int $studentId, int $termId, int $yearId, bool $approvedOnly = true): ?int
{
    $stmt = $pdo->prepare('SELECT form_id, stream_id FROM students WHERE id = ?');
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();
    if (!$student) {
        return null;
    }

    foreach (class_rankings($pdo, (int) $student['form_id'], (int) $student['stream_id'], $termId, $yearId, $approvedOnly) as $rank) {
        if ((int) $rank['id'] === $studentId) {
            return (int) $rank['position'];
        }
    }

    return null;
}
