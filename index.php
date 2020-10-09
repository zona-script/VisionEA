<!DOCTYPE html>
<?php
// include("./bnr/includes/inc_def.php");
// include("./bnr/includes/inc_conn.php");
// include("./bnr/includes/inc_functions.php");

$https = "";
$https = (isset($_SERVER['HTTPS'])?$_SERVER['HTTPS']:'off');
$https = 'on';
//echo $https; die();
//if (empty($_SERVER['HTTPS']) && ('on' != $_SERVER['HTTPS'])) {
if ($https != 'on'){
        $uri = 'https://';
        $uri .= $_SERVER['HTTP_HOST'];
        header('Location: '.$uri);
        die();
}else {
    
}
?>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description" content="VisionEA is a company that provides a smart platform for forex trading automatically.">
    <meta name="author" content="VisionEA">

    <title>VisionEA</title>

    <!-- Favicons -->
    <link rel="icon" href="./images/favicon.png" sizes="16x16 32x32" type="image/png">

    <!-- awesone fonts css-->
    <link href="./css/font-awesome.css" rel="stylesheet" type="text/css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script> -->

    <!-- all css here -->
    <link rel="stylesheet" href="./css/new_bootstrap.min.css">
    <!-- <link rel="stylesheet" href="./css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="./css/magnific-popup.css">
    <link rel="stylesheet" href="./owl-carousel/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="./css/et-line-icon.css">
    <!-- <link rel="stylesheet" href="./css/ionicons.min.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.0/css/ionicons.min.css">
    <link rel="stylesheet" href="./css/slick.css">
    <link rel="stylesheet" href="./css/bundle.css">
    <link rel="stylesheet" href="./css/new_style.css">
    <link rel="stylesheet" href="./css/responsive.css">
    <script src="./js/modernizr-2.8.3.min.js"></script>
    <script>
        $(document).ready(function(e) {

        });
    </script>

</head>
<body>
    <header class="transparent-bar absolute-bar header-area z-index ptb-40 sticker">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="header-left f-left">
                        <div class="logo">
                            <a href="index.php"><img src="./images/Logo-VisionEA-text.png" class="img-responsive" alt="" /></a>
                        </div>
                        <button class="navbar-toggle collapsed" data-toggle="collapse" data-target="#mynav">
                            <i class="ion-android-menu menu-open"></i>
                        </button>
                    </div>
                    <div class="header-right f-right">
                        <div class="bar-button f-right ml-15">
                            <a href="./bnr/member" class="header-btn">Login Now</a>
                        </div>
                        <div class="menu-bar f-right collapse navbar-collapse" id="mynav">
                            <nav>
                                <ul class="one-page">
                                    <li class="active"><a href="#slider-section">BERANDA</a></li>
                                    <li><a href="#about-area">TENTANG KAMI</a></li>
                                    <!-- <li><a href="#testimonials">testimonials</a></li> -->
                                    <li><a href="#produk">PRODUK KAMI</a></li>
                                    <li><a href="#contact">KONTAK</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- slider area -->
    <div id="slider-section" class="slider-wrapper" >
        <div class="single-slide height-100vh slide-one-bg">
            <div id="background-1" class="mouse-bg"></div>
            <div id="background-2" class="mouse-bg"></div>
            <div id="background-3" class="mouse-bg"></div>
            <div id="background-4" class="mouse-bg"></div>
            <div id="background-5" class="mouse-bg"></div>
            <div id="background-6" class="mouse-bg"></div>
            <div id="background-7" class="mouse-bg"></div>
            <div class="slide-caption">
                <div class="container">
                    <div class="slide-text">
                        <h2>Dari Sini <span>Langkah <br> Sukses</span> Anda dimulai</h2>
                        <p>Kami memberikan peluang besar meraih sukses dengan pendapatan yang menarik melalui penjualan ebook pembuatan aplikasi perdagangan otomatis yang dipasarkan melalui sistem penjualan langsung.</p>
                        <p>Anda berada di perusahaan yang tepat dengan pertumbuhan yang sangat pesat. Anda pun memiliki kesempatan yang besar bersama-sama kami tumbuh dan besar dengan income yang tak terbatas.</p>
                        <a href="./bnr/member/regLink.php" class="slider-btn">Join Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- slider area -->
    <!-- services area -->
    <div class="services-area pt-120">
        <div class="container">
            <div class="row coustom-row">
                <div class="col-md-4 col-sm-4 coustom-col">
                    <div class="single-service">
                        <span class="icon-global"></span>
                        <h3>Belajar Online</h3>
                        <p>Materi lengkap dan terpadu dengan mudah bisa diakses secara online. Dan tersedia contoh-contoh praktis yang bisa segera dicoba.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4 coustom-col">
                    <div class="single-service mrgn-res">
                        <span class="icon-genius"></span>
                        <h3>Mudah Dipahami</h3>
                        <p>E-book didesign khusus agar setiap orang bisa dengan mudah mempelajari dan menghasilkan aplikasi perdagangan keuangan otomatis dan memaksimalkan keuntungan perdagangan.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4 coustom-col">
                    <div class="single-service">
                        <span class="icon-bargraph"></span>
                        <h3>Mudah Diterapkan</h3>
                        <p>Team dengan pengalaman puluhan tahun mengembangkan aplikasi dan mendesign e-book lengkap dengan contoh kode dan penjelasan yang sangat mudah diterapkan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- about area -->
    <div id="about-area" class="about-area pt-60">
        <div class="container">
            <div class="row">
                <div class="col-md-7 col-sm-6">
                    <div class="about-content">
                        <h2>Tentang Kami <br><span>PT VisionEA Teknologi Perkasa</span></h2>
                        <p>PT VisionEA Teknologi Perkasa merupakan pertama dan satu-satunya perusahaan penjualan langsung yang berasal dari kota Pontianak yang memberikan pembelajaran pembuatan aplikasi otomatisasi perdagangan.</p>
                        <h2>Visi</h2>
                        <p>Menjadi perusahaan pemimpin dunia dalam Industri Penjualan Langsung dan menjadi nomor satu di industri Platform Edukasi Keuangan.</p>
                        <h2>Misi</h2>
                        <p>Menciptakan jutaan peluang dan menciptakan sumber penghasilan yang berkelanjutan dengan fokus pada perubahan jaman.</p> 
                        <!-- <div class="autograph">
                            <img src="images/team/2.png" alt="">
                        </div> -->
                        <!-- <div class="about-social">
                            <ul>
                                <li><a href="#"><i class="ion-social-facebook"></i></a></li>
                                <li><a href="#"><i class="ion-social-twitter"></i></a></li>
                                <li><a href="#"><i class="ion-social-linkedin"></i></a></li>
                                <li><a href="#"><i class="ion-social-instagram-outline"></i></a></li>
                            </ul>
                        </div> -->
                    </div>
                </div>
                <div class="col-md-5 col-sm-6">
                    <div class="about-img">
                        <!-- <img src="./images/team/1.png" alt=""> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="career-area pt-60 gray-bg" id="produk">
        <div class="container">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-9">
                    <div class="career-text text-left">
                        <h2>Produk Kami</h2>
                        <p>PT VisionEA Teknologi Perkasa adalah perusahaan penjualan langsung yang terdaftar resmi dan memiliki izin usaha penjualan langsung.</p>
                        <p>Perusahaan ini hanya menjual e-book pengembangan aplikasi otomatisasi perdagangan. Kami menyediakan beberapa serial e-book bagi seorang pemula hingga menjadi seorang yang alih.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="card" style="background-color: white; border-radius: 20px; padding: 10px;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5" style="padding: 30px;">
                                <!-- <div class="mockup-img"> -->
                                    <img src="./images/mockup/BASIC_EDITION.jpg" class="img-responsive" alt="">
                                <!-- </div> -->
                            </div>
                            <div class="col-md-7">
                                <div class="career-text">
                                    <h2>Basic Edition</h2>
                                    <p>E-book mahir membuat sistem perdagangan otomatis pada platform MetaTrader 4 dengan MQL Basic Edition adalah sebuah e-book yang mengajarkan pembuatan aplikasi dengan cara yang sangat sederhana.
                                    </p>
                                    <p>E-book ini merupakan rangkaian serial buku Pemogramman Expert Advisor. <br>
                                        Untuk memahami pembuatan aplikasi perdagangan otomatis, penjelasan dan contoh kode sederhana dan mudah dipahami dan diaplikasikan.
                                        <p>Dengan mempelajari e-book ini, diharapkan pembaca dapat membuat aplikasi perdagangan otomatis sederhana untuk membantu perdagangan forex.</p>
                                    <!-- <span>ONLY $39.00</span> -->
                                    <a href="./bnr/member/newRegLink.php">BELI BUKU</a>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            &nbsp;
            <div class="row">
                <div class="card" style="background-color: white; border-radius: 20px; padding: 10px;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5" style="padding: 30px;">
                                <!-- <div class="mockup-img"> -->
                                    <img src="./images/mockup/PRO_EDITION.jpg" class="img-responsive" alt="">
                                <!-- </div> -->
                            </div>
                            <div class="col-md-7">
                                <div class="career-text">
                                    <h2>Pro Edition</h2>
                                    <p>Pro Edition dari e-book Mahir membuat sistem perdagangan otomatis pada platform MetaTrader 4 dengan MQL memberikan penjelasan mendalam tentang memanipulasi pemogramman berbagai kondisi dan kebutuhan trading dan menyesuaikan karakteristik type akun ECN/STP.
                                    </P>
                                    <P>E-book ini merupakan rangkaian serial buku Pemogramman Expert Advisor.<br>
                                        Penjelasan yang disertai dengan contoh kode untuk memberikan gambaran perbedaan kondisi pasar trading dan karakteristik jenis akun semakin mempermudah pembaca memahami dan membuat aplikasi yang canggih untuk mendukung kebutuhan tradingnya dan juga mampu menjadi seorang programmer sistem perdagangan otomatis.</p>
                                    <!-- <span>ONLY $39.00</span> -->
                                    <a href="./bnr/member/newRegLink.php">BELI BUKU</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--
            <div class="row">
                <div class="card" style="background-color: white; border-radius: 20px; padding: 10px;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="mockup-img">
                                    <img src="./images/mockup/EXPERT_EDITION.jpg" class="img-responsive" alt="">
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="career-text">
                                    <h2>Expert Edition</h2>
                                    <p>Expert Edition dari E-book Mahir membuat sistem perdagangan otomatis pada platform MetaTrader 4 dengan MQL menghadirkan pembelajaran mendalam dan detil menyelesaikan berbagai kondisi perdagangan yang rumit, menggabungkan berbagai indikator standard maupun penggunaan indikator custom, pengembangan indikator custom dan berbagai fungsi manipulasi perdagangan dan penggabungan strategi trading yang rumit.</P>
                                    <P>Serial ketiga dari E-book Pemrograman Expert Advisor ini mampu memberikan bekal dan skill yang cukup untuk menjadi seorang programmer sistem perdagangan otomatis yang handal.
                                    </P>
                                    <a href="./bnr/member/newRegLink.php">BELI BUKU</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            -->
        </div>
    </div>
    <!-- <div id="testimonials" class="testimonials-area ptb-120">
        <div class="container">
            <div class="section-title text-center">
                <h2>Testimonials</h2>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="testimonial-image-slider text-center">
                        <div class="single-testi-text">
                            <p>The point of using Lorem Ipsum is that more-or-less normal distribution of letters, as opposed to using</p>
                        </div>
                        <div class="single-testi-text">
                            <p>The point of using Lorem Ipsum is that more-or-less normal distribution of letters, as opposed to using</p>
                        </div>
                        <div class="single-testi-text">
                            <p>The point of using Lorem Ipsum is that more-or-less normal distribution of letters, as opposed to using</p>
                        </div>
                        <div class="single-testi-text">
                            <p>The point of using Lorem Ipsum is that more-or-less normal distribution of letters, as opposed to using</p>
                        </div>
                    </div>
                    <div class="testimonial-text-slider text-center">
                        <div class="single-testi-img">
                            <img src="images/team/3.png" alt="testi 1" />
                            <h3>tayeb rayed</h3>
                            <h5>Designer</h5>
                        </div>
                        <div class="single-testi-img">
                            <img src="images/team/4.png" alt="testi 1" />
                            <h3>farhana shuvo</h3>
                            <h5>developer</h5>
                        </div>
                        <div class="single-testi-img">
                            <img src="images/team/5.png" alt="testi 1" />
                            <h3>nawaz sharif</h3>
                            <h5>Designer</h5>
                        </div>
                        <div class="single-testi-img">
                            <img src="images/team/3.png" alt="testi 1" />
                            <h3>md robiul</h3>
                            <h5>UX Designer</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <!-- <div class="subscribe-area bg-img ptb-150" style="background-image:url(images/bg/1.jpg);" data-overlay="8">
        <div class="container">
            <div class="subscribe-content text-center z-index">
                <h2>Berlanggan Berita</h2>
                <p>Ada banyak ilmu yang tersedia, tetapi sebagian besar kami akan memberikan info terbaru yang berguna untuk memahami dunia forex secara ekslusif.</p>
                <div class="subscribe-form text-center">
                    <div id="mc_embed_signup" class="subscribe-form">
                        <form action="#" method="" id="" name="" class="validate mc-form" novalidate onSubmit="return false;">
                            <div id="mc_embed_signup_scroll" class="mc-form">
                                <input type="email" value="" name="EMAIL" class="email" placeholder="Your email address" required>
                                <div class="mc-news" aria-hidden="true"><input type="text" name="b_6bbb9b6f5827bd842d9640c82_05d85f18ef" tabindex="-1" value=""></div>
                                <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <!-- <div id="pricing" class="pricing-area pt-120 pb-90">
        <div class="container">
            <div class="section-title section-title2 text-center">
                <h2>Pricing Plan</h2>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <div class="single-pricing text-center gray-bg-2 mb-30">
                        <div class="pricing-icon">
                            <i class="icon-genius"></i>
                        </div>
                        <div class="pricing-value-title">
                            <div class="pricing-value">
                                <h3>10 <span>$</span></h3>
                            </div>
                            <h4>basic</h4>
                        </div>
                        <div class="pricing-value-content">
                            <ul>
                                <li>Bonus Content</li>
                                <li>Download Access</li>
                                <li>Reduced Price</li>
                                <li>Free Support</li>
                            </ul>
                        </div>
                        <div class="pricing-btn">
                            <a href="#">BUY NOW</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="single-pricing text-center gray-bg-2 mb-30">
                        <div class="pricing-icon">
                            <i class="icon-strategy"></i>
                        </div>
                        <div class="pricing-value-title">
                            <div class="pricing-value">
                                <h3>15 <span>$</span></h3>
                            </div>
                            <h4>advanced</h4>
                        </div>
                        <div class="pricing-value-content">
                            <ul>
                                <li>Bonus Content</li>
                                <li>Download Access</li>
                                <li>Reduced Price</li>
                                <li>Free Support</li>
                            </ul>
                        </div>
                        <div class="pricing-btn">
                            <a href="#">BUY NOW</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="single-pricing text-center gray-bg-2 mb-30">
                        <div class="pricing-icon">
                            <i class="icon-wine"></i>
                        </div>
                        <div class="pricing-value-title">
                            <div class="pricing-value">
                                <h3>20 <span>$</span></h3>
                            </div>
                            <h4>premium</h4>
                        </div>
                        <div class="pricing-value-content">
                            <ul>
                                <li>Bonus Content</li>
                                <li>Download Access</li>
                                <li>Reduced Price</li>
                                <li>Free Support</li>
                            </ul>
                        </div>
                        <div class="pricing-btn">
                            <a href="#">BUY NOW</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <!-- <div class="video-counter-area gray-bg pt-110 pb-80">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="video-area">
                        <img src="images/banner/1.jpg" alt="">
                        <a href="https://www.youtube.com/watch?v=NV6RfexD_MQ" class="video-icon video-popup ">
                            <img src="images/banner/1.png" alt="">
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="counter-area pt-110">
                    <div class="col-md-3 col-sm-3">
                        <div class="single-counter mb-30">
                            <div class="counter-icon">
                                <i class=" icon-book-open"></i>
                            </div>
                            <div class="counter-text">
                                <h3 class="counter">120</h3>
                                <p>Total Pages</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="single-counter mb-30">
                            <div class="counter-icon">
                                <i class=" icon-profile-male"></i>
                            </div>
                            <div class="counter-text">
                                <h3 class="counter">150</h3>
                                <p>Active Readers</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="single-counter mb-30">
                            <div class="counter-icon">
                                <i class=" icon-heart"></i>
                            </div>
                            <div class="counter-text">
                                <h3 class="counter">850</h3>
                                <p>Facebook Fans</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="single-counter mb-30">
                            <div class="counter-icon">
                                <i class="icon-envelope"></i>
                            </div>
                            <div class="counter-text">
                                <h3 class="counter">540</h3>
                                <p>Subscribers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <!-- <div id="blog" class="blog-area pt-120 pb-90">
        <div class="container">
            <div class="section-title text-center">
                <h2>Our Blog</h2>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <div class="single-blog mb-30">
                        <div class="blog-img">
                            <a href="blog-details.html"><img src="images/blog/1.jpg" alt=""></a>
                        </div>
                        <div class="blog-info-all">
                            <div class="blog-info">
                                <h2><a href="blog-details.html">But I must explain to Helt.</a></h2>
                                <div class="admin-date">
                                    <span class="admin"><a href="#">By Admin</a></span>
                                    <span>May 05, 2017</span>
                                </div>
                                <p>On the other hand, we denounce with riition and dislike men who are so beguiled aorized by the charms of pleasure.</p>
                            </div>
                            <div class="blog-btn">
                                <a href="blog-details.html">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="single-blog mb-30">
                        <div class="blog-img">
                            <a href="blog-details.html"><img src="images/blog/2.jpg" alt=""></a>
                        </div>
                        <div class="blog-info-all">
                            <div class="blog-info">
                                <h2><a href="blog-details.html">Various versions have evolv.</a></h2>
                                <div class="admin-date">
                                    <span class="admin"><a href="#">By Admin</a></span>
                                    <span>May 05, 2017</span>
                                </div>
                                <p>On the other hand, we denounce with riition and dislike men who are so beguiled aorized by the charms of pleasure.</p>
                            </div>
                            <div class="blog-btn">
                                <a href="blog-details.html">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="single-blog mb-30">
                        <div class="blog-img">
                            <a href="blog-details.html"><img src="images/blog/3.jpg" alt=""></a>
                        </div>
                        <div class="blog-info-all">
                            <div class="blog-info">
                                <h2><a href="blog-details.html">If you are going to use a pas.</a></h2>
                                <div class="admin-date">
                                    <span class="admin"><a href="#">By Admin</a></span>
                                    <span>May 05, 2017</span>
                                </div>
                                <p>On the other hand, we denounce with riition and dislike men who are so beguiled aorized by the charms of pleasure.</p>
                            </div>
                            <div class="blog-btn">
                                <a href="blog-details.html">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <footer id="contact" class="footer-area black-bg" style="margin-bottom: 30px;">
        <div class="container">
            <div class="footer-top text-center">
                <div class="footer-logo" style="margin-bottom: 30px;">
                    <a href="#"><img src="./images/Logo-VisionEA-text.png" alt=""></a>
                </div>
                <!-- <div class="footer-menu">
                    <nav>
                        <ul>
                            <li><a href="#">FACEBOOK</a></li>
                            <li><a href="#">TWITTER</a></li>
                            <li><a href="#">LINKDIN</a></li>
                            <li><a href="#">INSTAGRAM</a></li>
                            <li><a href="#">PINTEREST</a></li>
                        </ul>
                    </nav>
                </div> -->
                <div class="contact">
                    <ul>
                        <li>EMAIL : <a href="mailto:support-id@visionea.net?Subject=Kontak%20Kami"> SUPPORT-ID@VISIONEA.NET</a></li>
                        <li>PHONE : +0561 8110234</li><br>
                        <li>
                            ALAMAT :
                        </li><br>
                        <li>
                            JALAN DANAU SENTARUM <br>
                            KELURAHAN SEI BANGKONG <br>
                            KECAMATAN PONTIANAK KOTA <br>
                            KOTA PONTIANAK KAL-BAR
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom text-center">
            <div class="container">
                <div class="copyright">
                    <p>Copyright© <a href="#" target="blank">VisionEA</a> 2020.All right reserved</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- all js here -->
    <script src="./js/jquery-1.12.0.min.js"></script>
    <script src="./js/new_bootstrap.min.js"></script>
    <script src="./js/jquery.magnific-popup.min.js"></script>
    <script src="./js/jquery.counterup.min.js"></script>
    <script src="./js/waypoints.min.js"></script>
    <script src="./js/ajax-mail.js"></script>
    <script src="./owl-carousel/owl.carousel.min.js"></script>
    <script src="./js/plugins.js"></script>
    <script src="./js/new_main.js"></script>
</body>    
</html>