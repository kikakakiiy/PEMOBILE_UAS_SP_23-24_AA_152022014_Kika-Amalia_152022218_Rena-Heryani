<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'config.php';

    $params = json_decode(trim(file_get_contents("php://input")), true) ? json_decode(trim(file_get_contents("php://input")), true) : $_POST;
    $id = 1;

    if (
        empty($params['title']) ||
        empty($params['description'])
    ) {
        $status = 400;
        $data = null;
    } else {
        $input = [
            "title" => $conn->real_escape_string($params['title']),
            "description" => $conn->real_escape_string($params['description']),
        ];

        if (!empty($id)) {
            $sql = "SELECT * FROM `biografi_dokter` WHERE id = " . $id;
            $result = $conn->query($sql);
            $isExists = [];
            while ($row = $result->fetch_assoc()) {
                $isExists = $row;
            }

            if (empty($isExists)) {
                $status = 500;
                $data = [
                    "status" => "failed",
                    "message" => "Data not found",
                    "data" => (object) []
                ];
            } else {
                $key = "";
                $total_string = "";
                $values = [];
                $no = 1;
                foreach ($input as $k => $v) {
                    if (count($input) == $no) {
                        $key .= $k . '=? ';
                    } else {
                        $key .= $k . '=?, ';
                    }
                    $total_string .= "s";
                    $values[] = $v;
                    $no++;
                }
                $total_string .= "i";
                $values[] = $id;

                $sql = "UPDATE `biografi_dokter` SET $key WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($total_string, ...$values);
                if ($stmt->execute()) {
                    $sql = "SELECT * FROM `biografi_dokter` WHERE id = " . $id;
                    $result = $conn->query($sql);
                    $payTableObj = (object) [];
                    while ($row = $result->fetch_assoc()) {
                        $payTableObj = $row;
                    }

                    $status = 200;
                    $data = [
                        "status" => "success",
                        "message" => "Data successfully updated",
                        "data" => $payTableObj
                    ];
                } else {
                    $data = null;
                    $status = 500;
                }
            }
        }
    }
} else {
    $data = null;
    $status = 405;
}

http_response_code($status);
echo json_encode($data);

include 'return.php';
