<?php

if ($pretty) {
    $text = __("Clef two-factor authentication", "wpclef");
} else {
    $text = __("WordPress Login Protected by Clef", "wpclef");
}

?>

<a href="https://getclef.com?utm_source=badge" class="clef-badge <?php if ($pretty) {?>pretty<?php } ?>" target="_blank"><?php echo $text ?></a>
<style>
.clef-badge {
    width: 100%;
    text-align: center;
    display: inline-block;
    margin: 10px auto;
}
.clef-badge * {
    box-sizing: border-box;
}
.clef-badge.pretty {
    display: block;
    overflow: hidden;
    text-indent: -579px;
    height: 50px;
    width: 140px;
    background: url("https://bit.ly/clef-wordpress-badge");
    background-size: 100% 100%;
    opacity: .8;
}
.clef-badge:hover {
    opacity: 1;
}
</style>
