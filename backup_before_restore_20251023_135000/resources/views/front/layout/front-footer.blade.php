<footer class="piclicks_footer">
    <div class="widget_wrapper">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-12" style="padding-right:30px;">
                    <div class="widget widegt_about">
                        <div class="widget_title">
                            <img src="{{ asset(env('LOGO_PATH', 'assets/images/logo.png')) }}" class="img-fluid" alt>
                        </div>
                        <p>It is a long established fact that a reader will be distracted by the readable content of a
                            page when looking at its layout. </p>

                    </div>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <div class="widget widget_link">
                        <div class="widget_title">
                            <h4>Products</h4>
                        </div>
                        <ul>
                            <li><a href="{{ route('front.upload-photos') }}">Design your Collage</a></li>
                            <li><a href="{{route('front.art-gallery')}}">Art Gallery </a></li>
                            <li><a href="{{route('front.giftcard')}}">Gift Card</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <div class="widget widget_link">
                        <div class="widget_title">
                            <h4>Follow us</h4>
                        </div>
                        <ul>
                            <li><a href="https://www.instagram.com/accounts/login/" target="_blank">Instagram</a></li>
                            <li><a href="https://www.facebook.com/" target="_blank">Facebook</a></li>
                            <li><a href="https://in.pinterest.com/" target="_blank">Pinterest</a></li>
                            <li><a href="https://www.youtube.com/" target="_blank">Youtube</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="widget widget_contact">
                        <div class="widget_title">
                            <h4>Support</h4>
                        </div>
                        <div class="contact_info">
                            <div class="">
                                <div class="info">
                                    <p>Need to Support. Please send me mail at:</p>
                                    <p><a href="">info@gmail.com</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright_area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="copyright_text">
                        <p>Copyright &copy; {{ date('Y') }} Piclicks. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- -----Footer End------------->


