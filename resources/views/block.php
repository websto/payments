<!--<link href="/css/app_.css" rel="stylesheet" type="text/css">
<script src="/js/app_.js"></script>-->
<style>

</style>

<section class="sidebar_payment">
    <?php if ($pay):?>
<div>Выбирете способ оплаты</div>
    <select onchange="payment_send(this)">
        <option>--</option>
        <?php foreach ($pay as $v): ?>
            <?php foreach ($v['title'] as $k=>$val): ?>
        <option value="<?php echo $k;?>"><?php echo $val;?></option>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>

</section>

<script>

    function payment_send (obj) {

        var xhr = new XMLHttpRequest();

        var value = obj.options[obj.selectedIndex].value;

        var h = document.querySelector("meta[name='csrf-token']").getAttribute("content");

        xhr.open('POST', 'payment', true);

        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.setRequestHeader("X-CSRF-TOKEN", h);

        xhr.onreadystatechange = function () {

            if (xhr.status != 200) {
                console.log(xhr.status + ': ' + xhr.statusText); // пример вывода: 404: Not Found
            } else {
                //console.log(xhr.responseText); // responseText -- текст ответа.
            }
        };
        xhr.send('val='+value);

    }

</script>