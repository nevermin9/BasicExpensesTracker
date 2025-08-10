<?php
// $navigation = [
//     "Home" => "/",
//     "Categories" => "/categories",
//     "Expenses" => "/expenses",
// ];
?>

<header>
    <nav>
        <ul>
            <?php foreach ($navigation as $name => $path): ?>
            <li>
                <a href="<?= $path ?>">
                    <?= $name ?>
                </a>
            </li>
            <?php endforeach ?>
        </ul>
    </nav>
</header>
