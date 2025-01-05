<div style="width: 100%; height: 40%">
    <form action="" method="post" target="eval_result">
        <textarea name="eval_code" style="width: 100%; height: 80%"></textarea>
        <input type="submit" value="eval">
    </form>
</div>


<iframe name="eval_result" style="width: 100%; height: 50%; background-color: #EEE;">

</iframe>

<script>
    var evalCodeTextarea = document.getElementsByName('eval_code')[0];

    document.addEventListener('DOMContentLoaded', function () {
        var value = localStorage.getItem('eval_code');
        if (value !== null) {
            console.log(value);
            evalCodeTextarea.value = value;
        }
    });

    evalCodeTextarea.addEventListener('keydown', function () {
        localStorage.setItem('eval_code', this.value);
    })
</script>