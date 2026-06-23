<?php
//  เพิ่มบรรทัดนี้ไว้บนสุดของไฟล์ nav.php เพื่อให้ระบบดึงคลาส LineLogin มาใช้งานได้บน Vercel
require_once('LineLogin.php');
?>

<div class="container">
    <header class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4 border-bottom"> 

        <div class="col-md-3 text-end">
            <?php
                // 💡 ปรับเงื่อนไขให้เช็กค่าว่างของโปรไฟล์ LINE ให้แม่นยำยิ่งขึ้น
                if (!isset($_SESSION['profile']) || empty($_SESSION['profile']) || $_SESSION['profile'] === null) {
                    $line = new LineLogin();
                    $link = $line->getLink();
            ?> 
            <a href="<?php echo $link; ?>" class="btn btn-success me-2"style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 20px; width: 100%;">Line Login</a>
            <?php } else { ?>
            <!-- 💡 เปลี่ยนชื่อลิงก์ปลายทางให้สะกดเป็นตัวพิมพ์เล็กทั้งหมดตามมาตรฐานเว็บจริงบน Vercel -->
            <a href="linelogout.php" class="btn btn-danger">Line Logout</a> 
            <?php } ?>
        </div> 

    </header>
</div>
