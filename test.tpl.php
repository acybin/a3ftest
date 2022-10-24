<html>
<head><title>Кол-во тегов</title></head>
<body>
<div>
    <?=$error?>
    <form action="test.php" method="post">
        <label>URL</label>
        <input type="text" name="url">
        <button type="submit">Расчитать</button>
    </form>
    <?php if (!empty($tags)):?>
        <h2>Количество тегов</h2>
        <ul>
            <?php foreach ($tags as $tag => $count):?>
                <li><?=$tag?> : <?=$count?></li>
            <?php endforeach;?>
        </ul>
    <?php endif;?>
</div>
</body>
</html>