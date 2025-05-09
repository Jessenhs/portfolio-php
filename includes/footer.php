</main>
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>My Portfolio</h5>
                    <p>A showcase of my projects and skills.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Contact</h5>
                    <p>Email: jessenieuwenhuis@hotmail.com</p>
                    <p>Phone: +31 6 15907203</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p>&copy; <?php echo date('Y'); ?> My Portfolio. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Close database connection
$db->closeConnection();
?>