    </div><!-- end content-area -->
</div><!-- end main-content -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.alert-dismissible').forEach(a => {
    setTimeout(() => { a.classList.add('fade'); setTimeout(() => a.remove(), 300); }, 4000);
});
</script>
</body>
</html>
