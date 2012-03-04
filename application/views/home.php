<html>
<body>
    <h1><?php echo $idea->name; ?></h1>
    <h3><a href="#<?php echo urlencode($idea->name); ?>-crock">Crock</a></h3>
    <?php echo $results['crock']; ?>
    <?php if ($proof && count($proof['crock']) > 0): ?>
        <h4>Proof:</h4>
        <ul>
            <?php foreach ($proof['crock'] as $url): ?>
            <li><?php echo $url; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <h3><a href="#<?php echo urlencode($idea->name); ?>-not">Not</a></h3>
    <?php echo $results['not']; ?>
    <?php if ($proof && count($proof['not']) > 0): ?>
        <h4>Proof:</h4>
        <ul>
            <?php foreach ($proof['not'] as $url): ?>
            <li><?php echo $url; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <hr>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script>
    $.post('/vote/',{id:1,crock:1},function(data){ console.log('AJAX Data',data); });
    </script>
</body>
</html>