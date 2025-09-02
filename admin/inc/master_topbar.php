<!-- Header -->
<div id="header">
    <div class="color-line">
    </div>
    <div id="logo" class="light-version">
        <span class="text-align : center;" >
               Youth Photographic Society
        </span>
    </div>
    <nav role="navigation" style="display : flex; align-items : center;">
        <div class="header-link hide-menu" style="padding: 10px 20px; font-size: 24px"><i class="pe pe-7s-angle-left-circle"></i></div>
		<?php
			if (isset($_SESSION['admin_yearmonth'])) {
				$sql = "SELECT contest_name FROM contest WHERE yearmonth = '" . $_SESSION['admin_yearmonth'] . "' ";
				$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				$row = mysqli_fetch_array($query);
		?>
		<span style="font-size: 24px;"><?=$row['contest_name'];?></span>
		<?php
			}
		?>
		<!---
        <form role="search" class="navbar-form-custom" method="post" action="#">
            <div class="form-group">
                <input type="text" placeholder="Search something special" class="form-control" name="search">
            </div>
        </form>
        <div class="mobile-menu">
            <button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
                <i class="fa fa-edit"></i>
            </button>
            <div class="collapse mobile-navbar" id="mobile-collapse">
                <ul class="nav navbar-nav">
                    <li><a class="" href="index.php">Logout</a></li>
                    <li><a data-toggle="modal" data-target="#myModal">Profile</a></li>
                </ul>
            </div>
        </div> -->
        <!-- <div class="navbar-right">
            <ul class="nav navbar-nav no-borders">
                <li class="dropdown">
                    <a class="dropdown-toggle" href="#" data-toggle="dropdown">
                        <i class="pe-7s-keypad"></i>
                    </a>

                    <div class="dropdown-menu hdropdown bigmenu animated flipInX">
                        <table>
                            <tbody>
                            <tr>
                                <td>
                                    <a data-toggle="modal" href="user_admin.php">
                                        <i class="pe pe-7s-user text-color"></i>
                                        <h5>User Administration</h5>
                                    </a>
                                </td>
                                <td>
                                    <a href="dashboard.php">
                                        <i class="pe pe-7s-graph3 text-color"></i>
                                        <h5>Dashboard</h5>
                                    </a>
                                </td>
                                <td>
                                    <a href="all_participate.php">
                                        <i class="pe pe-7s-users text-color"></i>
                                        <h5>Participants</h5>
                                    </a>
                                </td>
                                <td>
                                    <a href="results.php">
                                        <i class="pe pe-7s-ribbon text-color"></i>
                                        <h5>Results</h5>
                                    </a>
                                </td>
                            </tr>
                           </tbody>
                        </table>
                    </div>
                </li>
                <li>
                    <a href="#" id="sidebar" class="right-sidebar-toggle">
                        <i class="pe-7s-upload pe-7s-note"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="index.php">
                        <i class="pe-7s-upload pe-rotate-90"></i>
                    </a>
                </li>
            </ul>
        </div> -->
    </nav>
</div>
