<?php
$getPdo = require_once "../db/index.php";
$pdo = $getPdo();
$stmt = $pdo->query("SELECT id,name FROM categories;");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cols = ['timestamp', 'name', 'description', 'sum', 'type', 'category_id'];
$sql = "SELECT " . implode(", ", $cols) . " FROM expenses";

$options = [
    'nameDescSubstring' => isset($_GET['name_desc_substr']) ? trim($_GET['name_desc_substr']) : '',
    'fromTs' => isset($_GET['from_date']) ? strtotime($_GET['from_date']) : 0,
    'toTs' => isset($_GET['to_date']) ? strtotime($_GET['to_date']) : 0,
    'fromSum' => filter_input(INPUT_GET, 'from_sum', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
    'toSum' => filter_input(INPUT_GET, 'to_sum', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
    'type' => in_array(trim($_GET['type'] ?? ''), ['Expense', 'Income']) ? trim($_GET['type'] ?? '') : '',
    'category_id' => filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT),
];
$options = array_filter($options);
$optionsSql = [];

foreach ($options as $key => $val) {
    if ($key === 'nameDescSubstring') {
        $optionsSql[$key] = "(name LIKE CONCAT('%' :nameDescSubstring '%') OR description LIKE CONCAT('%' :nameDescSubstring '%'))";
    } elseif ($key === 'fromTs') {
        $optionsSql[$key] = "timestamp >= :fromTs";
    } elseif ($key === 'toTs') {
        $optionsSql[$key] = "timestamp <= :toTs";
    } elseif ($key === 'fromSum') {
        $optionsSql[$key] = "sum >= :fromSum";
    } elseif ($key === 'toTs') {
        $optionsSql[$key] = "sum <= :toSum";
    } else {
        // type, category_id
        $optionsSql[$key] = "$key = :$key";
    }
}

if (count($optionsSql) > 0) {
    $sql = $sql . " WHERE " . implode(" AND ", $optionsSql) . ";";
} else {
    $sql = $sql . ";";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($options);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getCategoryById(int $id): string
{
    global $categories;

    $category = array_find($categories, static fn($v, int $k) => $k === $id);
    return $category['name'] ?? '';
}
?>

<main>
    <h1>
        Expenses
    </h1>

    <div>
        <form action="/expenses" method="get">
            <input
                placeholder="Name's/description's substring"
                type="text"
                minlength="3"
                autocomplete="off"
                name="name_desc_substr"
            />

            <button>
                Filter
            </button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Description</th>
                <th>Sum</th>
                <th>Type</th>
                <th>Category</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($rows as $row): ?>
            <tr>
                <td>
                    <?php echo htmlspecialchars($row['name']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars(date("Y-m-d H:i:s", $row['timestamp'])); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($row['description']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($row['sum']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($row['type']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars(getCategoryById($row['category_id'])); ?>
                </td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</main>

