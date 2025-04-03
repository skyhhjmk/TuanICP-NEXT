<?php



function add_cron_job($hook, $schedule,$args = array(), $nextRun = null) {
    $pdo = initDatabase();
    $args = json_encode($args);
    $nextRun =$nextRun ?: date('Y-m-d H:i:s', strtotime($schedule));
    $stmt =$pdo->prepare("INSERT INTO cron_jobs (hook, schedule, args, next_run, status) VALUES (:hook, :schedule, :args, :next_run, 1)");
    $stmt->execute([':hook' =>$hook, ':schedule' => $schedule, ':args' =>$args, ':next_run' => $nextRun]);
}

function remove_cron_job($id) {
    $pdo = initDatabase();
    $stmt =$pdo->prepare("DELETE FROM cron_jobs WHERE id = :id");
    $stmt->execute([':id' =>$id]);
}

function toggle_cron_job_status($id, $status) {
    $pdo = initDatabase();
    $stmt =$pdo->prepare("UPDATE cron_jobs SET status = :status WHERE id = :id");
    $stmt->execute([':status' =>$status, ':id' => $id]);
}

function execute_cron_jobs() {
    $pdo = initDatabase();
    $stmt =$pdo->query("SELECT * FROM cron_jobs WHERE status = 1 AND next_run <= NOW()");
    while ($job =$stmt->fetch()) {
        // 执行任务
        call_user_func_array($job['hook'], json_decode($job['args'], true));

        // 更新任务下次执行时间和最后执行时间
        $nextRun = date('Y-m-d H:i:s', strtotime($job['schedule'], strtotime($job['next_run'])));
        $stmtUpdate =$pdo->prepare("UPDATE cron_jobs SET last_run = NOW(), next_run = :next_run WHERE id = :id");
        $stmtUpdate->execute([':next_run' =>$nextRun, ':id' => $job['id']]);
    }
}
