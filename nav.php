<?php
//  เพิ่มบรรทัดนี้ไว้บนสุดของไฟล์ nav.php เพื่อให้ระบบดึงคลาส LineLogin มาใช้งานได้บน Vercel
require_once('LineLogin.php');
?>

<div class="container">
    <header class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4 border-bottom"> 
            <a href="/" class="d-flex align-items-center col-md-3 mb-2 mb-md-0 text-dark text-decoration-none"> 
                Line Login Website
            </a> 
   
        <ul class="nav col-12 col-md-auto mb-2 justify-content-center mb-md-0">
            <li><a href="#" class="nav-link px-2 link-secondary">Home</a></li> 
            <li><a href="#" class="nav-link px-2">Features</a></li> 
            <li><a href="#" class="nav-link px-2">Pricing</a></li> 
            <li><a href="#" class="nav-link px-2">FAQs</a></li> 
            <li><a href="#" class="nav-link px-2">About</a></li> 
        </ul>

        <div class="col-md-3 text-end">
            <?php
                // 💡 ปรับเงื่อนไขให้เช็กค่าว่างของโปรไฟล์ LINE ให้แม่นยำยิ่งขึ้น
                if (!isset($_SESSION['profile']) || empty($_SESSION['profile']) || $_SESSION['profile'] === null) {
                    $line = new LineLogin();
                    $link = $line->getLink();
            ?> 
            <a href="<?php echo $link; ?>" class="btn btn-success me-2">Line Login</a>
            <?php } else { ?>
            <!-- 💡 เปลี่ยนชื่อลิงก์ปลายทางให้สะกดเป็นตัวพิมพ์เล็กทั้งหมดตามมาตรฐานเว็บจริงบน Vercel -->
            <a href="linelogout.php" class="btn btn-danger">Line Logout</a> 
            <?php } ?>
        </div> 

    </header>
</div>
