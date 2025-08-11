<?php
$getPdo = require_once "../db/index.php";
$pdo = $getPdo();
$stmt = $pdo->query('SELECT id,name FROM categories;');
$categoriesList = $stmt->fetchAll(PDO::FETCH_ASSOC);
$cols = [];
$errors = [];
$valid = true;
$success = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']))
{
    if (!isset($_POST['token']) || !hash_equals($_SESSION['csrf_token'], $_POST['token']))
    {
        exit('Something is wrong...');
    }

    $cols = [
        'timestamp' => strtotime($_POST['date']),
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'sum' => filter_input(INPUT_POST, 'sum', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'type' => trim($_POST['type']),
        'category_id' => filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT)
    ];

    $cols['sum'] = filter_var($cols['sum'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);

    if (empty($cols['name']) || strlen($cols['name']) < 3 || strlen($cols['name']) > 100)
    {
        $errors['Name'] = 'Invalid name';
    }

    if (!empty($cols['description']) && strlen($cols['description']) > 150)
    {
        $errors['Description'] = 'Description is too long';
    }

    if ($cols['sum'] === false)
    {
        $errors['Sum'] = 'Invalid sum';
    }

    if (!in_array($cols['type'], ['Expense', 'Income'], true))
    {
        $errors['Type'] = "Invalid type";
    }

    $categoriesIds = array_column($categoriesList, 'id');
    if ($cols['category_id'] === false || !in_array($cols['category_id'], $categoriesIds, true))
    {
        $errors['Category'] = "Invalid category";
    }

    $valid = empty($errors);

    if ($valid)
    {
        $stmt = $pdo->prepare(
            "INSERT INTO expenses(" . implode(", ", array_keys($cols)) . ")" . "VALUES(:" . implode(", :", array_keys($cols)) . ")"
        );

        $stmt->execute($cols);
        $_SESSION['success'] = true;
    } else {
        $_SESSION['errors'] = $errors;
        $_SESSION['cols'] = $cols;
        $_SESSION['valid'] = $valid;
    }

    header('Location: /', true, 303);
    die();
} 
else if ( $_SERVER['REQUEST_METHOD'] === 'GET' )
{
    if (isset($_SESSION['errors']))
    {
        $errors = $_SESSION['errors'];
        unset($_SESSION['erros']);
    }

    if (isset($_SESSION['cols']))
    {
        $cols = $_SESSION['cols'];
        unset($_SESSION['cols']);
    }

    if (isset($_SESSION['valid']))
    {
        $valid = $_SESSION['valid'];
        unset($_SESSION['valid']);
    }

    if (isset($_SESSION['success']))
    {
        $success = $_SESSION['success'];
        unset($_SESSION['success']);
    }
}
?>

<main>
    <h1>
        Add expense/income entry
    </h1>

    <?php if (!$valid): ?>
    <p>
        <span>
            Validation errors:
        </span>

        <ul>
            <?php foreach ($errors as $err): ?>
            <li>
                <span>
                    <?php echo htmlspecialchars($err) ?>
                </span>
            </li>
            <?php endforeach ?>
        </ul>
    <p>
    <?php endif ?>

    <?php if ($success): ?>
    <p id="success-msg">
        The entry was successfully added!
    <p>
    <?php endif ?>

    <form method="POST" action="/">
        <label>
            <span>Name*</span>

            <input 
                type="text"
                minlength="3"
                maxlength="100"
                name="name"
                autocomplete="false"
                placeholder="Laptop"
                required
                value="<?php echo htmlspecialchars($cols['name'] ?? ''); ?>"
            >
        </label>

        <label>
            <span>Description</span>

            <span>
                <span id="chars-counter" > 0 </span>

                <span> / 150</span>
            </span>

            <textarea 
                id="expense-desc"
                name="description"
                autocomplete="false"
                rows="1"
                placeholder="Optional"
                maxlength="150"
            ><?php echo htmlspecialchars($cols['description'] ?? ''); ?></textarea>
        </label>

        <label>
            <span>
                Sum*
            </span>

            <input
                type="number"
                name="sum"
                step="0.01"
                min="0"
                required
                placeholder="99.99"
                value="<?php echo htmlspecialchars($cols['sum'] ?? ''); ?>"
            />
        </label>

        <label>
            <span>Type</span>

            <select name="type" required>
                <option value="Expense" <?php if(($cols['type'] ?? '') === 'Expense') echo 'selected' ?>>Expense</option>
                <option value="Income" <?php if(($cols['type'] ?? '') === 'Income') echo 'selected' ?>>Income</option>
            </select>
        </label>

        <label>
            <span>
                Category*
            </span>

            <select name="category_id" required>
                <option value="" disabled <?php if(!isset($cols['category_id'])) echo 'selected' ?>>Please, select category</option>
                <?php foreach ($categoriesList as $c): ?>
                <option 
                    value="<?php echo htmlspecialchars($c['id']); ?>"
                    <?php if(($cols['category_id'] ?? '') === $c['id']) echo 'selected' ?>
                >
                    <?php echo htmlspecialchars($c['name']); ?>
                </option>
                <?php endforeach ?>
            </select>
        </label>

        <label>
            <span>Date</span>

            <input 
                id="dateinput"
                name="date"
                type="datetime-local"
                value="<?php 
                    if (!empty($cols['timestamp']))
                    {
                        echo htmlspecialchars(date("Y-m-d\TH:i", $cols['timestamp']));
                    }
                ?>"
            />
        </label>

        <input name="token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" hidden />

        <button
            name="submit"
        >
            Submit
        </button>
    </form>
</main>

<script type="text/javascript">
const dateinput = document.getElementById("dateinput");
if (!dateinput.value) {
    const now = new Date();

    const formatted = now.toISOString().slice(0,16); 
    const tzOffset = now.getTimezoneOffset() * 60000; 
    const localISO = new Date(now - tzOffset).toISOString().slice(0,16);

    dateinput.value = localISO;
}
</script>

<script type="text/javascript">
const charsCounterEl = document.getElementById("chars-counter");
const expenseDescEl = document.getElementById("expense-desc");
const updateCharsCounter = () => {
    charsCounterEl.innerText = expenseDescEl.value.length;
};
updateCharsCounter();
expenseDescEl.addEventListener("input", updateCharsCounter);
</script>
<script type="text/javascript">
const successMsgEl = document.getElementById("success-msg")
if (successMsgEl) {
    setTimeout(() => {
        successMsgEl.remove();
    }, 2000)
}
</script>


