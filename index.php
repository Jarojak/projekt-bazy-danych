<?php

function getDefaultCategories($conn)
{
    $sql = "SELECT *
            FROM incomes_category_default";

    $results = $conn->query($sql);

    return $results->fetchAll(PDO::FETCH_ASSOC);
}

function setDefaultCategoriesToUser($conn, $categories, $user_id)
{
    $sql = "INSERT INTO incomes_category_assigned_to_users (user_id, name)
            VALUES ";

    $values = [];

    foreach ($categories as $id) {
        $values[] = "($user_id, ?)";
    }

    $sql .= implode(", ", $values);

    $stmt = $conn->prepare($sql);
    
    foreach ($categories as $i => $category) {
        $stmt->bindValue($i + 1, $category["name"], PDO::PARAM_STR);
    }

    $stmt->execute();
}

function insertRandomIncomes($conn, $qty, $user_id)
{
    $sql = "INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income)
            VALUES ";

    $values = [];

    for ($i = 1; $i <= $qty; $i++) {
        $values[] = "($user_id, ?, ?, ?)";
    }

    $sql .= implode(", ", $values);

    $stmt = $conn->prepare($sql);
    
    foreach ($values as $i => $iter) {
        
        $value_amount = mt_rand(1,8000) + 0.01 *mt_rand(0,99);

        if($i > 0) { $i *= 3; }
        $stmt->bindValue($i + 1, rand(5,8), PDO::PARAM_INT);
        $stmt->bindValue($i + 2, strval($value_amount), PDO::PARAM_STR);
        $stmt->bindValue($i + 3, date("Y-m-d H:i:s",mt_rand(1672531200,1680307200)), PDO::PARAM_STR);
    }

    $stmt->execute();
}

function getAmountByCategories($conn, $user_id, $date_begin, $date_end)
{
    $sql = "SELECT 
                incomes_category_assigned_to_users.name AS category, 
                SUM(incomes.amount) AS amount 
            FROM 
                incomes_category_assigned_to_users 
            INNER JOIN 
                incomes ON 
                incomes.income_category_assigned_to_user_id 
                = incomes_category_assigned_to_users.id 
            WHERE 
                incomes.user_id = :user_id
                AND 
                incomes.date_of_income BETWEEN :date_begin AND :date_end
            GROUP BY incomes.income_category_assigned_to_user_id 
            ORDER BY amount DESC;";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->bindValue(':date_begin', $date_begin, PDO::PARAM_STR);
    $stmt->bindValue(':date_end', $date_end, PDO::PARAM_STR);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require 'includes/init.php';

$conn = require 'includes/db.php';

//$results = getDefaultCategories($conn);
//setDefaultCategoriesToUser($conn,$results,1);

//insertRandomIncomes($conn, 12, 1);

var_dump(getAmountByCategories($conn,1,'2023-01-01','2023-02-01'));

?>
<?php require 'includes/header.php'; ?>

<?php require 'includes/footer.php'; ?>
