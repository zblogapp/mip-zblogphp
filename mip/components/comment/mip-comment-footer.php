<script>
  (function () {
    function postMessage (data) {
      window.top.postMessage(Object.assign({
        from: 'zblogphp'
      }, data), '*');
    }
    function resize() {
      postMessage({event: 'resize', height: window.document.body.scrollHeight});
    }
    window.addEventListener("resize", function () { resize() })
    window.addEventListener("load", function () { resize() })
    postMessage({event: 'viewnums', id: '{$article.ID}', value: '{$article.ViewNums}'})
  })()

</script>
