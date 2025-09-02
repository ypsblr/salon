<?php
// Access to all Salon Catalogs
include_once("inc/session.php");


function fmtd ($str) {
	return date("F jsi", strtotime($str));
}
$catalog_list = array(
					array("year" => 2021, "salon_name" => "YPS All India Salon 2021", "patronage" => "FIP 2021/FIP/127/2021",
						  "chairman" => "Prema Kakade", "secretary" => "Krishna Bhat",
						  "jury" => "A G Lakshminarayan, AG Gangadhar, Digwas Bellemane, Hira Punjabi, Ravindranath Mallula, Sudhir Saxena, Vaibhav Shrikant Jaguste",
						  "exhibition_venue" => "Chitrakala Parishath", "exhibition_from" => "2021-08-20", "exhibition_to" => "2021-08-22",
						  "img" => "YPS_2021_AIS_039.jpg", "catalog" => "YPS_2021_AIS_039.pdf", "download" => "YPS_2021_AIS_039_D.pdf"),

					array("year" => 2020, "salon_name" => "YPS International Salon 2020", "patronage" => "FIAP 2020/525, PSA 2020-5466, GPU L200175, MoL 2020/59, FIP 2020/FIP/189/2020",
		  				  "chairman" => "H Satish", "secretary" => "Manju Vikas Sastry V",
		  				  "jury" => "Susan Cowles, Barbara Schmidt, Ana Joveva, Leif Alveen, Mohammed Arfan Asif, Pandula Bandara, B Srinivasa, Barun Sinha, Vinay Parelkar, Elena McTighe, Larry Cowles, Subrata Bysack",
		  				  "exhibition_venue" => "Virtual", "exhibition_from" => "2021-01-16", "exhibition_to" => "2021-01-31",
		  				  "img" => "YPS_2020_IS_011.jpg", "catalog" => "YPS_2020_IS_011.pdf", "download" => "YPS_2020_IS_011_D.pdf"),

					array("year" => 2020, "salon_name" => "YPS All India Digital Salon 2020", "patronage" => "FIP 2020/FIP/99/2020",
					  	  "chairman" => "Girish Ananthamurthy", "secretary" => "Prema Kakade",
					  	  "jury" => "Digwas Bellemane, Dinesh Allamaprabhu, Gurdas Dua, K S Srinivas, Parthasarathy Sarkar, Pinaki Ranjan Talukder, Santosh K Jana, Sunil Kapadia, Vinay Parelkar",
					  	  "exhibition_venue" => "Virtual", "exhibition_from" => "2020-07-26", "exhibition_to" => "2020-08-31",
					  	  "img" => "YPS_2020_AIS_038.jpg", "catalog" => "YPS_2020_AIS_038.pdf", "download" => "YPS_2020_AIS_038_D.pdf"),

					array("year" => 2019, "salon_name" => "YPS 37th All India Digital Salon 2019", "patronage" => "FIP 2019/FIP/210/2019",
		  				  "chairman" => "Manju Vikas Sastry V", "secretary" => "Girish Ananthamurthy",
		  				  "jury" => "A G Lakshminarayan, B Srinivasa, Krishna Bhat, T A Jayakumar, Threesh Kapoor, Anitha Mysore",
		  				  "exhibition_venue" => "Chitrakala Parishath", "exhibition_from" => "2019-12-21", "exhibition_to" => "2019-12-22",
		  				  "img" => "YPS_2019_AIS_037.jpg", "catalog" => "YPS_2019_AIS_037.pdf", "download" => "YPS_2019_AIS_037_D.pdf"),

		  			array("year" => 2019, "salon_name" => "YPS International Salon 2019", "patronage" => "FIAP 2019/294, PSA 2019-212, GPU L190057, MoL 2019/24, FIP 2019/FIP/44/2019",
		  				  "chairman" => "H Satish", "secretary" => "Murali Santhanam",
		  				  "jury" => "Mohammed Arfan Asif, S R Mandal, Suniel Marathe, S Thippeswamy, A K Raju, Diinesh Kumble",
		  				  "exhibition_venue" => "Chitrakala Parishath", "exhibition_from" => "2019-08-24", "exhibition_to" => "2019-08-25",
		  				  "img" => "YPS_2019_IS_010.jpg", "catalog" => "YPS_2019_IS_010.pdf", "download" => "YPS_2019_IS_010_D.pdf"),

					array("year" => 2018, "salon_name" => "YPS 36th All India Digital Salon 2018", "patronage" => "FIP 2018/FIP/142/2018",
						  "chairman" => "Manju Vikas Sastry V", "secretary" => "Girish Ananthamurthy",
						  "jury" => "A G Gangadhar, Astro Mohan, C R Sathyanarayana, Digwas Bellemane, Nagendra Muthmurdu, Prasanna Venkatesh Gubbi",
						  "exhibition_venue" => "Chitrakala Parishath", "exhibition_from" => "2019-01-05", "exhibition_to" => "2019-01-06",
						  "img" => "YPS_2018_AIS_036.jpg", "catalog" => "YPS_2018_AIS_036.pdf", "download" => "YPS_2018_AIS_036.pdf"),

				  	array("year" => 2018, "salon_name" => "YPS International Salon 2018", "patronage" => "FIAP 2018/328, ICS 2018/171, GPU L180062, IUP 2018/020, FIP 2018/FIP/25/2018",
						  "chairman" => "H Satish", "secretary" => "Murali Santhanam",
						  "jury" => "Adit Agarwala, Dr. Shivji Joshi, A G Lakshminarayan, M N Jayakumar, B Srinivasa, Dr. Manoj Sindagi",
						  "exhibition_venue" => "Chitrakala Parishath", "exhibition_from" => "2018-08-17", "exhibition_to" => "2018-08-19",
						  "img" => "YPS_2018_IS_009.jpg", "catalog" => "YPS_2018_IS_009.pdf", "download" => "YPS_2018_IS_009_D.pdf"),

					array("year" => 2017, "salon_name" => "35th YPS All India Salon 2017", "patronage" => "FIP/146/2017",
					  	  "chairman" => "Manju Vikas Sastry V", "secretary" => "Murali Santhanam",
						  "jury" => "A G Lakshminarayan, Dr. Barun Kumar Sinha, Dr. Bhupesh C Little, Diinesh Kumble, Prasanna Venkatesh G, Praveen Kumar H V",
						  "exhibition_venue" => "Chitrakala Parishath", "exhibition_from" => "2018-01-13", "exhibition_to" => "2018-01-14",
						  "img" => "YPS_2017_AIS_035.jpg", "catalog" => "YPS_2017_AIS_035.pdf", "download" => "YPS_2017_AIS_035_D.pdf"),

				  	array("year" => 2017, "salon_name" => "YPS International Digital Salon 2017", "patronage" => "FIAP 2017/334, PSA 2017-223, FIP 2017/FIP/07/2017",
						  "chairman" => "H Satish", "secretary" => "Murali Santhanam",
						  "jury" => "M N Jayakumar, Dhritiman Mukherjee, Sudhir Shivaram, Susanta Banerjee, Anil Risal Singh, Mohammed Arfan Asif",
						  "exhibition_venue" => "Chitrakala Parishath", "exhibition_from" => "2017-08-19", "exhibition_to" => "2017-08-21",
						  "img" => "YPS_2017_IS_008.jpg", "catalog" => "YPS_2017_IS_008.pdf", "download" => "YPS_2017_IS_008.pdf"),

				  array("year" => 2016, "salon_name" => "34th YPS All India Digital Salon of Photography 2016", "patronage" => "FIP/47/2016",
					  	  "chairman" => "K S Srinivas", "secretary" => "H V Ramachandran",
						  "jury" => "Dr. Manoj C Sindagi, A K Raju, A G Gangadhar, T Kempanna, K Gopinath, H K Rajashekar, U B Pavanaja",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "2016-11-18", "exhibition_to" => "2016-11-20",
						  "img" => "YPS_2016_AIS_034.jpg", "catalog" => "YPS_2016_AIS_034.pdf", "download" => "YPS_2016_AIS_034.pdf"),

					array("year" => 2011, "salon_name" => "33rd YPS All India Salon of Photography 2011", "patronage" => "FIP/41/2011",
						  "chairman" => "K S Srinivas", "secretary" => "H V Ramachandran",
						  "jury" => "H N Allama Prabhu, Anil Risal Singh, M S Hebbar, A G Lakshminarayana, TNA Perumal, A K Raju",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "2012-03-08", "exhibition_to" => "2012-03-12",
						  "img" => "YPS_2011_AIS_033.jpg", "catalog" => "YPS_2011_AIS_033.pdf", "download" => "YPS_2011_AIS_033.pdf"),

					array("year" => 2010, "salon_name" => "32nd YPS All India Salon of Photography 2010", "patronage" => "FIP/29/2010",
						  "chairman" => "H V Praveen Kumar", "secretary" => "L Narasimha Prakash",
						  "jury" => "H V Praveen Kumar, T N A Perumal, CR Sathyanarayana, B Rajan Babu, G Harinarayana",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "2011-02-10", "exhibition_to" => "2011-02-13",
						  "img" => "YPS_2010_AIS_032.jpg", "catalog" => "YPS_2010_AIS_032.pdf", "download" => "YPS_2010_AIS_032.pdf"),

					array("year" => 2009, "salon_name" => "31st YPS All India Salon of Photography 2009", "patronage" => "FIP/25/2009",
						  "chairman" => "H V Praveen Kumar", "secretary" => "L Narasimha Prakash",
						  "jury" => "H V Praveen Kumar, T N A Perumal, CR Sathyanarayana, B Rajan Babu, G Harinarayana",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "2010-02-05", "exhibition_to" => "2010-02-08",
						  "img" => "YPS_2009_AIS_031.jpg", "catalog" => "YPS_2009_AIS_031.pdf", "download" => "YPS_2009_AIS_031.pdf"),

					array("year" => 2008, "salon_name" => "30th YPS All India Salon of Photography 2008", "patronage" => "FIP/23/2008",
						  "chairman" => "C R Sathyanarayana", "secretary" => "K S Srinivas",
						  "jury" => "B Srinivasa, H V Praveen Kumar, G S Ravi, M N Jayakumar, Gautam Basak",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "2009-02-21", "exhibition_to" => "2009-02-24",
						  "img" => "YPS_2008_AIS_030.jpg", "catalog" => "YPS_2008_AIS_030.pdf", "download" => "YPS_2008_AIS_030.pdf"),

					array("year" => 2007, "salon_name" => "29th YPS All India Salon of Photography 2007", "patronage" => "FIP",
						  "chairman" => "H V Praveen Kumar", "secretary" => "G Harinarayana",
						  "jury" => "B Srinivasa, Anil Risal Singh, H Satish, A G Lakshminarayana, H V Praveen Kumar",
						  "exhibition_venue" => "Kannada Bhavan", "exhibition_from" => "2007-12-21", "exhibition_to" => "2007-12-25",
						  "img" => "YPS_2007_AIS_029.jpg", "catalog" => "YPS_2007_AIS_029.pdf", "download" => "YPS_2007_AIS_029.pdf"),

					array("year" => 2005, "salon_name" => "28th YPS All India Salon of Photography 2005", "patronage" => "FIP",
						  "chairman" => "K A Suryaprakash", "secretary" => "K S Srinivas",
						  "jury" => "TNA Preumal, B Srinivasa, H Satish, M S Hebbar, M Janardhan, Dr. T Shivanandappa, V Nagaraja",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "2005-03-11", "exhibition_to" => "2005-03-13",
						  "img" => "YPS_2005_AIS_028.jpg", "catalog" => "YPS_2005_AIS_028.pdf", "download" => "YPS_2005_AIS_028.pdf"),

					array("year" => 2003, "salon_name" => "27th All India Salon of Photography 2003", "patronage" => "FIP/N-18/2003",
						  "chairman" => "B N Dayananda", "secretary" => "V S Kashinath",
						  "jury" => "TNA Preumal, R Dev, Dr. T Shivanandappa, B Srinivasa, M Janaredhan, V Nagaraja, T L Ramaswamy, T L Prabhakar, Nagesh Hegde",
						  "exhibition_venue" => "Chitrakala Parishath", "exhibition_from" => "2003-02-06", "exhibition_to" => "2003-02-08",
						  "img" => "YPS_2003_AIS_027.jpg", "catalog" => "YPS_2003_AIS_027.pdf", "download" => "YPS_2003_AIS_027.pdf"),

					array("year" => 2002, "salon_name" => "26th All India Salon of Photography 2002", "patronage" => "FIP/14/2002",
						  "chairman" => "M S Hebbar", "secretary" => "N S Ranganathan",
						  "jury" => "H N Allama Prabhu, A Ishwarayya, H V Praveen Kumar, K S Rajaram, H K Rajashekar, G S Ravi, H Satish, B Srinivasa, S Thippeswamy",
						  "exhibition_venue" => "Kannada Bhavan", "exhibition_from" => "2002-11-29", "exhibition_to" => "2002-12-01",
						  "img" => "YPS_2002_AIS_026.jpg", "catalog" => "YPS_2002_AIS_026.pdf", "download" => "YPS_2002_AIS_026.pdf"),

					array("year" => 2001, "salon_name" => "25th All India Salon of Photography 2001", "patronage" => "FIP/N-23/2001",
						  "chairman" => "H V Praveen Kumar", "secretary" => "N S Ranganathan",
						  "jury" => "M Janardhanan, T A Jayakumar, Nagesh Hegde, A K Raju, G S Ravi, Sunil S Kapadia, M S Venkatachalam",
						  "exhibition_venue" => "Kannada Bhavan", "exhibition_from" => "2001-11-30", "exhibition_to" => "2001-12-02",
						  "img" => "YPS_2001_AIS_025.jpg", "catalog" => "YPS_2001_AIS_025.pdf", "download" => "YPS_2001_AIS_025.pdf"),

					array("year" => 2000, "salon_name" => "24th All India Salon on Photography 2000", "patronage" => "FIP/2000/22",
						  "chairman" => "K S Rajaram", "secretary" => "N S Ranganathan",
						  "jury" => "M Janardhanan, T A Jayakumar, Nagesh Hegde, A K Raju, G S Ravi, Sunil S Kapadia, M S Venkatachalam",
						  "exhibition_venue" => "Kannada Bhavan", "exhibition_from" => "2000-11-04", "exhibition_to" => "2000-11-07",
						  "img" => "YPS_2000_AIS_024.jpg", "catalog" => "YPS_2000_AIS_024.pdf", "download" => "YPS_2000_AIS_024.pdf"),

					array("year" => 1999, "salon_name" => "23rd All India Salon on Photography 1999", "patronage" => "FIP/99/23",
						  "chairman" => "G S Krishnamurthy", "secretary" => "V S Kashinath",
						  "jury" => "S Nagaraj, B Srinivasa, S Thippeswamy, H Satish, K Gopinathan",
						  "exhibition_venue" => "Kannada Bhavan", "exhibition_from" => "1999-10-30", "exhibition_to" => "1999-11-01",
						  "img" => "YPS_1999_AIS_023.jpg", "catalog" => "YPS_1999_AIS_023.pdf", "download" => "YPS_1999_AIS_023.pdf"),

					array("year" => 1998, "salon_name" => "22nd All India Salon on Photography 1998", "patronage" => "FIP/98/23",
						  "chairman" => "M Venkataswamappa", "secretary" => "Dr. Shailesh A V Rao",
						  "jury" => "Dr. S Harinarayan, M S Hebbar, C R Sathyanarayana, R Dev, G S Krishnamurthy",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1998-12-03", "exhibition_to" => "1998-12-06",
						  "img" => "YPS_1998_AIS_022.jpg", "catalog" => "YPS_1998_AIS_022.pdf", "download" => "YPS_1998_AIS_022.pdf"),

					array("year" => 1997, "salon_name" => "21st All India Salon of Photography 1997", "patronage" => "FIP/97/24",
						  "chairman" => "S Nagesh", "secretary" => "RS Suresh",
						  "jury" => "Dr. S Harinarayan, M Janardhan, E Hanumantha Rao, Vivek R Sinha, S Thippeswamy",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1997-10-29", "exhibition_to" => "1997-11-02",
						  "img" => "YPS_1997_AIS_021.jpg", "catalog" => "YPS_1997_AIS_021.pdf", "download" => "YPS_1997_AIS_021.pdf"),

					array("year" => 1996, "salon_name" => "20th All India Salon of Photography 1996", "patronage" => "FIP",
						  "chairman" => "H Satish", "secretary" => "S Chandrashekar",
						  "jury" => "B S Sundaram, G S Ravi, H Satish, R Dev, K Jayaram, Thakur Dalip Singh",
						  "img" => "YPS_1996_AIS_020.jpg", "catalog" => "YPS_1996_AIS_020.pdf", "download" => "YPS_1996_AIS_020.pdf"),

					array("year" => 1995, "salon_name" => "19th YPS All India Salon of Photography 1995", "patronage" => "FIP/95/11",
						  "chairman" => "G S Krishnamurthy", "secretary" => "S Nagesh",
						  "jury" => "S Nagaraj, TNA Perumal, AK Raju, Sridhar, N Sundarraj",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1995-09-04", "exhibition_to" => "1995-09-07",
						  "img" => "YPS_1995_AIS_019.jpg", "catalog" => "YPS_1995_AIS_019.pdf", "download" => "YPS_1995_AIS_019.pdf"),

					array("year" => 1994, "salon_name" => "18th All India Salon of Photography 1994", "patronage" => "FIP/94/24",
						  "chairman" => "K S Rajaram", "secretary" => "K R Umesh",
						  "jury" => "E Hanumatha Rao, B Srinivasa, Dr. D V Rao, Susantha Banerjee, S Thippeswamy",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1994-11-19", "exhibition_to" => "1994-11-23",
						  "img" => "YPS_1994_AIS_018.jpg", "catalog" => "YPS_1994_AIS_018.pdf", "download" => "YPS_1994_AIS_018.pdf"),

					array("year" => 1993, "salon_name" => "17th All India Salon of Photography 1993", "patronage" => "FIP/93/25",
						  "chairman" => "K S Rajaram", "secretary" => "K R Umesh",
						  "jury" => "E Hanumatha Rao, TNA Perumal, B Srinivasa, B S Sundaram, Waman Thakre",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1993-10-15", "exhibition_to" => "1993-10-18",
						  "img" => "YPS_1993_AIS_017.jpg", "catalog" => "YPS_1993_AIS_017.pdf", "download" => "YPS_1993_AIS_017.pdf"),

					array("year" => 1992, "salon_name" => "16th All India Salon of Photography 1992", "patronage" => "FIP/92/20",
						  "chairman" => "K S Rajaram", "secretary" => "K R Umesh",
						  "jury" => "MS Hebbar, K Prabhakar, Dr. D V Rao, H Satish, N Sudar Raj",
						  "exhibition_venue" => "Karnataka Chitrakala Parishat", "exhibition_from" => "1992-09-10", "exhibition_to" => "1992-09-13",
						  "img" => "YPS_1992_AIS_016.jpg", "catalog" => "YPS_1992_AIS_016.pdf", "download" => "YPS_1992_AIS_016.pdf"),

					array("year" => 1991, "salon_name" => "15th All India Salon of Photography 1991", "patronage" => "FIP/91/21",
						  "chairman" => "M S Hebbar", "secretary" => "N S Ranganathan",
						  "jury" => "R A Acharya, Bhudev Bhagat, S Balasubramanya, E Hanumantha Rao, TNA Perumal",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1991-09-21", "exhibition_to" => "1991-09-24",
						  "img" => "YPS_1991_AIS_015.jpg", "catalog" => "YPS_1991_AIS_015.pdf", "download" => "YPS_1991_AIS_015.pdf"),

					array("year" => 1990, "salon_name" => "14th All India Salon of Photography 1990", "patronage" => "FIP/90/20",
						  "chairman" => "M S Hebbar", "secretary" => "N S Ranganathan",
						  "jury" => "S Balasubramanya, R Dev, B Rajan Babu, Dr. D V Rao, B Srinivasa, S Thippeswamy",
						  "exhibition_venue" => "Karnataka Chitrakala Parishath", "exhibition_from" => "1990-11-21", "exhibition_to" => "1990-11-24",
						  "img" => "YPS_1990_AIS_014.jpg", "catalog" => "YPS_1990_AIS_014.pdf", "download" => "YPS_1990_AIS_014.pdf"),

					array("year" => 1989, "salon_name" => "13th All India Salon of Photography 1989", "patronage" => "FIP/89/22",
						  "chairman" => "M S Hebbar", "secretary" => "N S Ranganathan",
						  "jury" => "S Balasubramanya, Bhaskar Ghosh, Dr. S Harinarayana, M Janardhanan, B Srinivasa, S Thippeswamy",
						  "exhibition_venue" => "Karnataka Chitrakala Parishath", "exhibition_from" => "1989-09-14", "exhibition_to" => "1989-09-17",
						  "img" => "YPS_1989_AIS_013.jpg", "catalog" => "YPS_1989_AIS_013.pdf", "download" => "YPS_1989_AIS_013.pdf"),

					array("year" => 1988, "salon_name" => "12th All India Salon of Photography 1988", "patronage" => "FIP/88/24",
						  "chairman" => "M S Hebbar", "secretary" => "N S Ranganathan",
						  "jury" => "S Balasubramanya, M S Hebbar, M Janardhanan, N Sundar Raj",
						  "exhibition_venue" => "Karnataka Chitrakala Parishath", "exhibition_from" => "1988-11-11", "exhibition_to" => "1988-11-14",
						  "img" => "YPS_1988_AIS_012.jpg", "catalog" => "YPS_1988_AIS_012.pdf", "download" => "YPS_1988_AIS_012.pdf"),

					array("year" => 1987, "salon_name" => "11th Salon of Photography 1987", "patronage" => "FIP/87/22",
						  "chairman" => "M S Hebbar", "secretary" => "K R Umesh",
						  "jury" => "T R Babu, S Nagaraj, TNA Perumal, E Hanumantha Rao, N Sundar Raj",
						  "exhibition_venue" => "State Youth Centre", "exhibition_from" => "1987-10-23", "exhibition_to" => "1987-10-27",
						  "img" => "YPS_1987_AIS_011.jpg", "catalog" => "YPS_1987_AIS_011.pdf", "download" => "YPS_1987_AIS_011.pdf"),

					array("year" => 1986, "salon_name" => "X All India Salon of Photography", "patronage" => "FIP",
						  "chairman" => "Mahantesh C Morabad", "secretary" => "K R Umesh",
						  "jury" => "T R Babu, S Nagaraj, TNA Perumal, E Hanumantha Rao, N Sundar Raj",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1986-09-12", "exhibition_to" => "1986-09-16",
						  "img" => "YPS_1986_AIS_010.jpg", "catalog" => "YPS_1986_AIS_010.pdf", "download" => "YPS_1986_AIS_010.pdf"),

					array("year" => 1985, "salon_name" => "IX All India Salon of Photograph 1985", "patronage" => "FIP/85/9",
						  "chairman" => "M S Hebbar", "secretary" => "K R Umesh",
						  "exhibition_venue" => "Visveswaraya Industrial & Technology Museum", "exhibition_from" => "1985-12-27", "exhibition_to" => "1985-12-31",
						  "img" => "YPS_1985_AIS_009.jpg", "catalog" => "YPS_1985_AIS_009.pdf", "download" => "YPS_1985_AIS_009.pdf"),

					array("year" => 1984, "salon_name" => "8th All India Salon 1984", "patronage" => "FIP/84/9",
						  "chairman" => "M S Hebbar", "secretary" => "J D Simons",
						  "jury" => "T R Babu, S Nagaraj, TNA Perumal, E Hanumantha Rao, N Sundar Raj",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1984-08-10", "exhibition_to" => "1984-08-14",
						  "img" => "YPS_1984_AIS_008.jpg", "catalog" => "YPS_1984_AIS_008.pdf", "download" => "YPS_1984_AIS_008.pdf"),

					array("year" => 1983, "salon_name" => "7th All India Salon 1983", "patronage" => "FIP/83/11",
						  "chairman" => "M S Hebbar", "secretary" => "B Lakshminarayana",
						  "jury" => "T R Babu, S Nagaraj, TNA Perumal, E Hanumantha Rao, N Sundar Raj",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1983-09-02", "exhibition_to" => "1983-09-06",
						  "img" => "YPS_1983_AIS_007.jpg", "catalog" => "YPS_1983_AIS_007.pdf", "download" => "YPS_1983_AIS_007.pdf"),

					array("year" => 1982, "salon_name" => "6th All India Salon 1982", "patronage" => "FIP/82/11", "chairman" => "M S Hebbar",
						  "jury" => "T R Babu, S Nagaraj, TNA Perumal, E Hanumantha Rao, T Kasinath, N Sundar Raj",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1982-05-28", "exhibition_to" => "1982-06-01",
						  "img" => "YPS_1982_AIS_006.jpg", "catalog" => "YPS_1982_AIS_006.pdf", "download" => "YPS_1982_AIS_006.pdf"),

					array("year" => 1981, "salon_name" => "Fifth All India Salon 1981", "patronage" => "FIP", "chairman" => "M S Hebbar",
						  "jury" => "T R Babu, S Nagaraj, TNA Perumal, V Gopalakrishnan, Dr. D V Rao",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1981-06-10", "exhibition_to" => "1981-06-14",
						  "img" => "YPS_1981_AIS_005.jpg", "catalog" => "YPS_1981_AIS_005.pdf", "download" => "YPS_1981_AIS_005.pdf"),

					array("year" => 1980, "salon_name" => "4th All India Salon 1980", "chairman" => "M S Hebbar", "patronage" => "FIP",
						  "jury" => "T R Babu, S Nagaraj, E Hanumantha Rao, TNA Perumal, V Gopalakrishnan",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1980-06-13", "exhibition_to" => "1980-06-17",
						  "img" => "YPS_1980_AIS_004.jpg", "catalog" => "YPS_1980_AIS_004.pdf", "download" => "YPS_1980_AIS_004.pdf"),

					array("year" => 1979, "salon_name" => "Third All India Salon 1979", "chairman" => "M S Hebbar",
						  "jury" => "T R Babu, S Nagaraj, Dr. G Thomas, E Hanumantha Rao, TNA Perumal",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1979-04-27", "exhibition_to" => "1979-05-01",
						  "img" => "YPS_1979_AIS_003.jpg", "catalog" => "YPS_1979_AIS_003.pdf", "download" => "YPS_1979_AIS_003.pdf"),

					array("year" => 1978, "salon_name" => "Second All India Salon 1978", "chairman" => "M S Hebbar",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1978-05-19", "exhibition_to" => "1978-05-23",
						  "img" => "YPS_1978_AIS_002.jpg", "catalog" => "YPS_1978_AIS_002.pdf", "download" => "YPS_1978_AIS_002.pdf"),

					array("year" => 1977, "salon_name" => "All India Salon 1977", "chairman" => "M S Hebbar",
						  "jury" => "TNA Perumal, C Rajagopal, Dr. G Thomas",
						  "exhibition_venue" => "Venkatappa Art Gallery", "exhibition_from" => "1977-03-25", "exhibition_to" => "1977-03-29",
						  "img" => "YPS_1977_AIS_001.jpg", "catalog" => "YPS_1977_AIS_001.pdf", "download" => "YPS_1977_AIS_001.pdf" ),
					);
?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>
<style>
.img-responsive {
	min-width: 60px;
	min-height: 60px;
}
.containerBox {
    position: relative;
    display: inline-block;
	width: 100%;
}
.thumbnail a > img {
	min-width: 80px;
	min-height: 80px;
}
</style>
</head>

<body class="<?php echo THEME;?>">

    <?php include_once("inc/navbar.php") ;?>
    <div class="wrapper">
		<!-- Jumbotron -->
		<?php  include_once("inc/Slideshow.php") ;?>
		<!-- Slideshow -->

		<div class="container-fluid intro">
			<div class="row">
				<!-- LEFT COLUMN -->
				<div class="col-sm-8 col-md-8 col-lg-8" style="padding-left:3%">
					<h2 class="headline first-child text-color">
						<span class="border-color">All Salon Catalogs</span>
					</h2>
					<p style="text-align:justify;">Youth Photographic Society has a rich heritage of conducting Salons since 1977.
						We could finally pull together all the old Salon Catalogs in one place. You can see from these catalogs
						how YPS was nurtured into a healthy Photography Club by some of the top names in the field of photography
						at that time.
					</p>
					<div class="row">
					<?php
						$index = 0;
						foreach ($catalog_list as $catalog) {
							$cover_page = "catalog/img/" . $catalog['img'];
							$tool_tip = "";
							$tool_tip .= isset($catalog['jury']) ? "Jury : [" . $catalog['jury'] . "]. " : '';
							$tool_tip .= isset($catalog['exhibition_venue']) ? "   Exhibited at ". $catalog['exhibition_venue'] . " from " . print_date($catalog['exhibition_from']) . " to " . print_date($catalog['exhibition_to']) : "";
					?>
						<div class="col-sm-3 thumbnail">
							<div class="caption text-center">
								<a href="#" data-toggle="tooltip"
										title="<?=$tool_tip;?>">
									<b><?=$catalog['salon_name'];?></b>
								</a>
								<br>
								<span class="text-info"><small><?= isset($catalog['patronage']) ? $catalog['patronage'] : ""; ?></small></span>
							</div>
							<div style="max-width:100%;" >
								<a href="viewer/viewer.html?file=/catalog/<?= $catalog['catalog'];?>#magazineMode=true" target="_blank" >
									<img class="img-responsive" style="margin-left:auto; margin-right:auto;" src="/catalog/img/<?= $catalog['img'];?>" >
								</a>
							</div>
							<div class="caption">
								<small><i class="fa fa-users text-muted" data-toggle="tooltip" title="Chairman"></i></small> <?= $catalog['chairman'];?>
								<?= isset($catalog['secretary']) ? "<br><small><i class='fa fa-drivers-license text-muted' data-toggle='tooltip' title='Secretary'></i></small> " . $catalog['secretary'] : "";?>
							</div>
							<div><a class="btn btn-color" style="width: 100%;" href="catalog/<?= $catalog['download']; ?>" download >Download</a></div>
						</div>
					<?php
							++ $index;
							if ($index % 4 == 0) {
					?>
						<div class="clearfix"></div>
					<?php
							}
						}
					?>
					</div>

				</div>
				<!-- END OF LEFT COLUMN -->

				<!-- RIGHT COLUMN -->
				<div class="col-sm-4 col-md-4 col-lg-4"  >
					<!-- Show Login Form -->
					<?php include("inc/login_form.php");?>

					<!-- Start Count Down One Week before the last date -->
					<?php include("inc/countdown.php");?>

					<!-- Partners -->
					<?php
						if (file_exists("./salons/$contest_yearmonth/blob/partners.php"))
							include("./salons/$contest_yearmonth/blob/partners.php");
					?>

					<!-- Show Catalog Download / View Links after results are published -->
					<?php if ($contestHasCatalog && ! is_null($catalogReleaseDate) && DATE_IN_SUBMISSION_TIMEZONE >= $catalogReleaseDate)
							include("inc/catalogview.php");
					?>

		            <!-- Image Carousel -->
					<style>
					.carousel-inner img{ max-height:300px !important; }
					</style>
				</div>		<!-- END OF RIGHT SIDE -->
			</div>	<!-- row -->
			<!-- FOOTER -->
			<div class="row">
				<?php include_once("inc/footer.php") ;?>
			</div>
		</div> <!-- / .container -->
    </div> <!-- / .wrapper -->

    <!-- Style Toggle -->

	<?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>
	<script>
		$(document).ready(function() {
			$("#login_login_id").hide();
			$("#check_it").attr("placeholder", "Email (or YPS Member ID)");
		});
	</script>


</body>

</html>
