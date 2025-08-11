<header>
    <nav>
        <ul>
            <?php foreach ($GLOBALS['navigation'] as $name => $path): ?>
            <li>
                <a href="<?= $path ?>">
                    <?= $name ?>
                </a>
            </li>
            <?php endforeach ?>
        </ul>
    </nav>
</header>
