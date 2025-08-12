<?php
$getPdo = require_once "../db/index.php";
$pdo = $getPdo();
$stmt = $pdo->query("SELECT id,name FROM categories;");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$isAdditionSuccess = false;
$isDeletionSuccess = false;
$isValid = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!isset($_POST['token']) || !hash_equals($_SESSION['csrf_token'], $_POST['token']))
    {
        exit("Something went wrong...");
    }

    // addition
    if (isset($_POST['add_new']))
    {
        $newCategoryName = trim($_POST['category_name']);

        if (empty($newCategoryName) || mb_strlen($newCategoryName) < 3 || mb_strlen($newCategoryName) > 100)
        {
            $isValid = false;
        }

        if ($isValid)
        {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt->execute(['name' => $newCategoryName]);
            $_SESSION['is_addition_success'] = true;
        }
        else
        {
            $_SESSION['is_valid'] = false;
        }
    }
    // deletion
    else if (isset($_POST['delete']))
    {
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);

        if ($categoryId !== false)
        {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->execute(['id' => $categoryId]);
            $_SESSION['is_deletion_success'] = true;
        }
        else
        {
            $_SESSION['is_deletion_success'] = false;
        }
    }

    header("Location: /categories", true, 303);
    die();
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    if (isset($_SESSION['is_valid']))
    {
        $isValid = $_SESSION['is_valid'];
        unset($_SESSION['is_valid']);
    }

    if (isset($_SESSION['is_addition_success']))
    {
        $isAdditionSuccess = $_SESSION['is_addition_success'];
        unset($_SESSION['is_addition_success']);
    }

    if (isset($_SESSION['is_deletion_success']))
    {
        $isDeletionSuccess = $_SESSION['is_deletion_success'];
        unset($_SESSION['is_deletion_success']);
    }
}
?>

<main>
    <h1>
        Categories
    </h1>

    <?php if ($isAdditionSuccess || $isDeletionSuccess): ?>
    <p id="success-msg">
        The category was successfully <?php echo ($isDeletionSuccess ? 'deleted' : 'added');  ?>!
    </p>
    <?php endif ?>

    <form action="/categories" method="POST">
        <?php if (!$isValid): ?>
        <p>
            The name is not valid
        </p>
        <?php endif ?>

        <label>
            <span>
                New category
            </span>

            <input
                type="text"
                name="category_name"
                minlength="3"
                maxlength="100"
                required
                placeholder="Games"
                autocomplete="off"
            />

            <input name="token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" hidden />

            <button name="add_new" value="add_new">Add new</button>
        </label>
    </form>

    <table>
        <thead>
            <tr>
                <th>Your categories</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($categories as $category): ?>
            <tr>
                <td>
                    <?php echo htmlspecialchars($category['name']); ?>
                </td>

                <td>
                    <form action="/categories" method="POST">
                        <input name="token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" hidden />
                        <input hidden name="category_id" value="<?php echo htmlspecialchars($category['id']); ?>" />
                        <button name="delete" value="delete">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</main>

<script type="text/javascript">
const successMsgEl = document.getElementById("success-msg")
if (successMsgEl) {
    setTimeout(() => {
        successMsgEl.remove();
    }, 2000)
}
</script>
