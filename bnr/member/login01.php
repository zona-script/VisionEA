<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />

<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
</head>

<body>
    <div class="container">
        <div class="container-fluid col-lg-6 col-md-10">
                <div class="row" style="padding-top:20%">
                    <div class="card">
                        
                        <div class="card-header card-header-rose card-header-icon">
                            <div class="card-icon">
                              <i class="material-icons">account_box</i>
                            </div>
                            <h4 class="card-title">Authentication</h4>
                        </div> <!-- end card-header -->
                        
                        <div class="card-body ">
                            <form class="form-horizontal">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Username</label>
                                    <div class="col-md-9">
                                      <div class="form-group has-default">
                                          <input type="text" name="username" class="form-control" required>
                                      </div>
                                    </div>
                                </div>
                    
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Password</label>
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                	<div class="col-md-3"></div>
                                    <div class="col-md-9">
                                         <img id="siimage" align="left" border: "0" src="../captcha.php?sid=<?php echo md5(time()); ?>" /> 
                                         <!-- pass a session id to the query string of the script to prevent ie caching -->
                            
                                            <a tabindex="-1" style="border-style: none" href="#" title="Refresh Image" onclick="document.getElementById('siimage').src = '../captcha.php?sid=' + Math.random(); return false">
                                            <img src="../assets/img/captcha/refresh_captcha.jpg" height="50px" alt="Reload Image" border="0" onclick="this.blur()" align="bottom" />
                                            </a>
                                        <label for="in_code">Secure Code</label>
							            <input type="text" name="code" id="in_code">
                                    </div>
                                </div> <!-- end row -->
                    
                                <div class="row">
                                    <label class="col-md-3"></label>
                    
                                    <div class="col-md-9">
                                      <div class="form-check">
                                          <label class="form-check-label">
                                              <input class="form-check-input" type="checkbox" value="">Remember me
                                              <span class="form-check-sign">
                                                  <span class="check"></span>
                                              </span>
                                          </label>
                                      </div>
                                    </div>
                                </div>
                                
                                <div class="row col-md-12">
                                    <div class="col-md-3"></div>
                                    <div class="col-md-9">
                                        <button type="submit" class="btn btn-fill btn-rose">Sign in</button>
                                    </div>
                                </div>
                            </form>
                        </div> <!-- end body card -->
                        <div class="card-footer fa-align-right">
                            forget password
                        </div> <!-- end card-footer -->
                        
                    </div> <!-- end card -->
                </div> <!-- end row -->
        </div>
    </div>
</body>
</html>