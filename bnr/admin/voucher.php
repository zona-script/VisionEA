<?php
$page = (isset($_GET['page']))? $_GET['page'] : 1;
$pageActive = (isset($_GET['pageActive']))? $_GET['pageActive'] : 1;
$numPerPage = $DEF_NUM_PER_PAGE;
?>
<div class="col-sm-12">
    <div class="well">
	<?php
		$sql 	= "SELECT count(vCode) totalRec FROM dtVoucher INNER JOIN msStatus on stID = vStatus ";
		$sql 	.= " WHERE vStatus= '" . $DEF_STATUS_ACTIVE . "'";
		$query = $conn->query($sql);
		$row = $query->fetch_assoc();
		$totalRec = $row['totalRec'];
		$numPages	= ceil ($totalRec / $numPerPage);			
		$startRec = ($pageActive-1) * $numPerPage;
	?>

        <div class="col-md-12 subTitle text-primary">PIN Active</div>
        <table class="table table-striped table-hover">
            <thead>
                <th class="col-md-1">#</th>
                <th class="col-md-5">PIN Code</th>
                <th class="col-md-2">Date</th>
                <th class="col-md-2">Status</th>
                <th class="col-md-2">Update By</th>
            </thead>
            <tfoot>
                <th class="col-md-1">#</th>
                <th class="col-md-5">PIN Code</th>
                <th class="col-md-2">Date</th>
                <th class="col-md-2">Status</th>
                <th class="col-md-2">Update By</th>
            </tfoot>
            <tbody>
                <?php
                    $sql 	= "SELECT vCode, vDate, stDesc, vUploadBy FROM dtVoucher INNER JOIN msStatus on stID = vStatus ";
                    $sql 	.= " WHERE vStatus= '" . $DEF_STATUS_ACTIVE . "'";
					$sql	.= " limit " . $startRec . ", " . $numPerPage;
                    $query = $conn->query($sql);
                    $no = 0;
                    while ($row = $query->fetch_assoc()){
                ?>
                <tr>
                    <td class="col-md-1"><?php echo ++$no; ?></td>
                    <td class="col-md-5"><?php echo $row["vCode"] ?></td>
                    <td class="col-md-2"><?php echo $row["vDate"] ?></td>
                    <td class="col-md-2"><?php echo $row["stDesc"] ?></td>
                    <td class="col-md-2"><?php echo $row["vUploadBy"] ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <!-- pagination -->
        <div class="row text-right">
            <ul class="pagination">
            	<?php 
					$prev = $next = "";	
					if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
                    if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
				?>
                <li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu=voucher&pageActive=' . $pagePrev); ?>">Previous</a></li>
                <?php 
				for ($i=1; $i<=$numPages; $i++){
					$active = "";
					if ($i == $pageActive) $active = "active";
                echo "<li class='" . $active . "'><a href='./?menu=voucher&pageActive=$i'>$i</a></li>";
				}
				?>
                <li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu=voucher&pageActive=' . $pageNext); ?>">Next</a></li>
            </ul>&nbsp;&nbsp;&nbsp;&nbsp;
		</div>  
</div>
<div class="well">
	<?php
		$sql 	= "SELECT count(vCode) totalRec FROM dtVoucher INNER JOIN msStatus on stID = vStatus ";
		$sql 	.= " WHERE vStatus= '" . $DEF_STATUS_USED . "'";
		$query = $conn->query($sql);
		$row = $query->fetch_assoc();
		$totalRec = $row['totalRec'];
		$numPages	= ceil ($totalRec / $numPerPage);			
		$startRec = ($page-1) * $numPerPage;
	?>
        <div class="col-md-12 subTitle text-primary">PIN Used</div>
        <table class="table table-striped table-hover">
            <thead>
                <th class="col-md-1">#</th>
                <th class="col-md-5">PIN Code</th>
                <th class="col-md-2">Date</th>
                <th class="col-md-2">Status</th>
                <th class="col-md-2">Update By</th>
            </thead>
            <tfoot>
                <th class="col-md-1">#</th>
                <th class="col-md-5">PIN Code</th>
                <th class="col-md-2">Date</th>
                <th class="col-md-2">Status</th>
                <th class="col-md-2">Update By</th>
            </tfoot>
            <tbody>
                <?php
                    $sql 	= "SELECT vCode, vDate, stDesc, vUploadBy FROM dtVoucher INNER JOIN msStatus on stID = vStatus ";
                    $sql 	.= " WHERE vStatus= '" . $DEF_STATUS_USED . "'";
					//$sql	.= " limit 0, 10"; // limit (page-1) * numPerPage, numPerPage
					$sql	.= " limit " . $startRec . ", " . $numPerPage;
                    $query = $conn->query($sql);
                    $no = 0;
                    while ($row = $query->fetch_assoc()){
                ?>
                <tr>
                    <td class="col-md-1"><?php echo ++$no; ?></td>
                    <td class="col-md-5"><?php echo $row["vCode"] ?></td>
                    <td class="col-md-2"><?php echo $row["vDate"] ?></td>
                    <td class="col-md-2"><?php echo $row["stDesc"] ?></td>
                    <td class="col-md-2"><?php echo $row["vUploadBy"] ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <!-- pagination -->
        <div class="row text-right">
            <ul class="pagination">
            	<?php 
					$prev = $next = "";	
					if ($page <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $page-1;
                    if ($page >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $page+1;
				?>
                <li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu=voucher&page=' . $pagePrev); ?>">Previous</a></li>
                <?php 
				for ($i=1; $i<=$numPages; $i++){
					$active = "";
					if ($i == $page) $active = "active";
                echo "<li class='" . $active . "'><a href='./?menu=voucher&page=$i'>$i</a></li>";
				}
				?>
                <li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu=voucher&page=' . $pageNext); ?>">Next</a></li>
            </ul>&nbsp;&nbsp;&nbsp;&nbsp;
		</div>  
    </div>
</div>