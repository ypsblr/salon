<?php
    // Data for communicating tracking number to award winners and catalog orders

    $tracking_site = "https://www.indiapost.gov.in";
    $posts_name = "India Posts";
    $mail_format = "mailing_info_email.htm";
    $mailing_data = array(
            // Awards Mailed and Tracking No sent to participants on 25th January 2022 - Info sent on 26th January 2022
            // mailing_info_email.htm
			// Indian Receipients
            // ["skb.orion@gmail.com", "TRAVEL - FIAP Gold", "2022/01/25", "EK029492096IN", "" ], // Balachander SK
			// ["basu.prasenjit@gmail.com", "COLOR - FIP Ribbon", "2022/01/25", "EK029499521IN", "" ], // Prasenjit Basu
			// ["risadaby@gmail.com", "MONOCHROME - FIP Ribbon", "2022/01/25", "EK029491847IN", "" ], // Sadiq Ur Rehman
			// ["jineshprasadphotosalon@gmail.com", "TRAVEL - ICS Silver", "2022/01/25", "EK029491780IN", "" ], // Jinesh Prasad
			// ["skgproposals@yahoo.com", "COLOR - FIP Ribbon", "2022/01/25", "EK029491776IN", "" ], // Soumen Kumar Ghosh
			// ["subhamoy.chaki@yahoo.com", "COLOR - MoL Silver", "2022/01/25", "EK029491904IN", "" ], // Subhomoy Chaki
			// ["jguha.1992@gmail.com", "NATURE - YPS Ribbon", "2022/01/25", "EK029491918IN", "" ], // Jayanth Guha
			// ["sthippeswamymysore@gmail.com", "TRAVEL - FIP Ribbon", "2022/01/25", "EK029491921IN", "" ], // Thippeswamy
			// ["balu76in@gmail.com", "TRAVEL - MoL Gold Ribbon", "2022/01/25", "EK029491895IN", "" ], // Balasubramanian G V
			// ["kaj_dasgupta@rediffmail.com", "NATURE - GPU Ribbon", "2022/01/25", "EK029491793IN", "" ], // Kajari Das Gupta
			// ["gogoibinoy@gmail.com", "MONOCHROME - MoL Bronze", "2022/01/25", "EK029491802IN", "" ], // Binoy Bikash
			// ["suvankar.bagchi20@gmail.com", "TRAVEL - YPS Ribbon", "2022/01/25", "EK029498551IN", "" ], // Suvankar Bagchi
			// ["subhasishbhandari@gmail.com", "NATURE - GPU Ribbon", "2022/01/25", "EK029492405IN", "" ], // Subasish Bandari
			// ["sharmabc@gmail.com", "NATURE - MoL Bronze Ribbon", "2022/01/25", "EK029492153IN", "" ], // Sharma B C
			// ["vikoscura@gmail.com", "TRAVEL - FIP Ribbon", "2022/01/25", "EK029492721IN", "" ], // Avik Dutta
			// ["maj_anup@yahoo.com", "COLOR - MoL Gold", "2022/01/25", "EK029493911IN", "" ], // Anup Majumdar
			// ["satyajitdas100@gmail.com", "COLOR - FIP Ribbon", "2022/01/25", "EK029492140IN", "" ], // Satyajit Das
			// ["bhrigubayan.mahalaxmi@gmail.com", "MONOCHROME - FIP Ribbon", "2022/01/25", "EK029492269IN", "" ], // Bhrigu Kumar
			// ["srsupratim@gmail.com", "TRAVEL - ICS Gold", "2022/01/25", "EK029492388IN", "" ], // Supratim Roy
			// ["mantu_mitra@rediffmail.com", "TRAVEL - FIP Ribbon", "2022/01/25", "EK029492374IN", "" ], // Piyali Mitra
			// ["gayatristudiioraju@gmail.com", "MONOCHROME - FIP Ribbon", "2022/01/25", "EK029492136IN", "" ], // Mereti Somaraju
			// ["yogi.badri8@gmail.com", "NATURE - FIP Ribbon", "2022/01/25", "EK029492255IN", "" ], // Yogesh B
			// ["jbkamdarsalons@gmail.com", "TRAVEL - MoL Bronze", "2022/01/25", "EK029492312IN", "" ], // Jayendra Babubhai
			// ["mr.prasenjit84@gmail.com", "MONOCHROME - ICS Bronze", "2022/01/25", "EK029492431IN", "" ], // Prasenjit Das
			// ["wildwitness@gmail.com", "NATURE - YPS Ribbon", "2022/01/25", "EK029492184IN", "" ], // Nagendra
			// ["ponagantirajupatel@gmail.com", "TRAVEL - FIP Ribbon", "2022/01/25", "EK029492309IN", "" ], // Raju Ponaganti
			// ["arabindabasak1974@gmail.com", "COLOR - GPU Ribbon", "2022/01/25", "EK029492428IN", "" ], // Arabinda Basak
			// ["bidhan164@gmail.com", "NATURE - GPU Ribbon", "2022/01/25", "EK029492175IN", "" ], // Bidhan
			// ["Paragbanerjee7@gmail.com", "COLOR - MoL Gold Ribbon|COLOR - PSA Ribbon", "2022/01/25", "EK029492290IN", "" ], // Parag Banerjee
			// ["mrrinalksen27@gmail.com", "TRAVEL - GPU Ribbon", "2022/01/25", "EK029492414IN", "" ], // Mrinal Sen
			// ["santanubose003@gmail.com", "MONOCHROME - GPU Gold", "2022/01/25", "EK029492082IN", "" ], // Santanu Bose
			// ["prasenjitsanyal2019@gmail.com", "TRAVEL - GPU Gold", "2022/01/25", "EK029492122IN", "" ], // Prasenjit Sanyal
			// ["saikat.chaterjee@gmail.com", "COLOR - YPS Silver", "2022/01/25", "EK029492051IN", "" ], // Saikat Chattopadhyay
			// ["pkar51@gmail.com", "COLOR - GPU Gold|COLOR - PSA Bronze|TRAVEL - FIP Ribbon", "2022/01/25", "EK029492048IN", "" ], // Priyanka Kar
			// ["dipanjannathphotography@gmail.com", "NATURE - PSA Silver", "2022/01/25", "EK029492025IN", "" ], // Dipanjan Nath
			// ["shravanabm@gmail.com", "MONOCHROME - PSA Gold", "2022/01/25", "EK029492034IN", "" ], // Shravan B M
			// ["koshalbasu@gmail.com", "NATURE - YPS Silver", "2022/01/25", "EK029492017IN", "" ], // Koshal Basu
			// ["roy.hereiam@gmail.com", "TRAVEL - YPS Gold", "2022/01/25", "EK029492065IN", "" ], // Partha Roy
			// ["anidebo@gmail.com", "COLOR - FIAP Gold", "2022/01/25", "EK029492119IN", "" ], // Anirban Chakrabarty
			// ["sanjayjodhpur22photography@gmail.com", "TRAVEL - PSA Silver", "2022/01/25", "EK029492079IN", "" ], // Sanjay Joshi
			// ["ajithuilgol@gmail.com", "NATURE - YPS Bronze", "2022/01/25", "EK029492105IN", "" ], // Ajit Huilgol
			// ["malaybasu12@gmail.com", "COLOR - FIP Ribbon", "2022/01/25", "EK029492167IN", "" ], // Malay Basu
			// ["anupam40402@gmail.com", "NATURE - ICS Gold", "2022/01/25", "EK029492286IN", "" ], // Anupam Chakraborty
			// ["sandippramanick35@gmail.com", "TRAVEL - GPU Ribbon", "2022/01/25", "EK029491966IN", "" ], // Sandip Paramani
			// ["dey0001@gmail.com", "COLOR - GPU Ribbon", "2022/01/25", "EK029491949IN", "" ], // Pinku Dey
			// ["pragita052211@gmail.com", "NATURE - ICS Silver", "2022/01/25", "EK029491833IN", "" ], // Prabir Kumar
			// ["drrana0207@yahoo.com", "TRAVEL - MoL Gold|COLOR - FIP Ribbon|TRAVEL - ICS Best Participant - Travel Section", "2022/01/25", "EK029491970IN", "" ], // Rana Jabeen Nawab
			// ["santusus79@gmail.com", "TRAVEL - MoL Silver Ribbon", "2022/01/25", "EK029491878IN", "" ], // Budhdheb Adhikary
			// ["asimphotogr@gmail.com", "COLOR - GPU Ribbon", "2022/01/25", "EK029491881IN", "" ], // Asim Kumar Bhattacharjee
			// ["chinmoybhattacharjee149@gmail.com", "COLOR - FIP Ribbon", "2022/01/25", "EK029491864IN", "" ], // Chinmoy Bhattacharjee
			// ["anitabasak70@gmail.com", "MONOCHROME - GPU Ribbon", "2022/01/25", "EK029491983IN", "" ], // Anita Basak
			// ["bardhansubhankarpiu@gmail.com", "NATURE - FIAP Ribbon|NATURE - FIP Ribbon", "2022/01/25", "EK029491997IN", "" ], // Subhankar Bardhan
			// ["amitdrbhatia@hotmail.com", "MONOCHROME - YPS Ribbon", "2022/01/25", "EK029492003IN", "" ], // Deep Bhatia
			// ["ankitporwalindore@gmail.com", "COLOR - FIP Ribbon", "2022/01/25", "EK029491816IN", "" ], // Ankit Porwal
			// ["dr.anirban.ash@gmail.com", "MONOCHROME - FIP Medal", "2022/01/25", "EK029491935IN", "" ], // Anirban Ash
            // ["anil_deshpande90@yahoo.com", "MONOCHROME - FIP Ribbon", "2022/01/25", "EK029491820IN", "" ], // Anil Kumar


            // International Recipients
            // ["viterson@box.nl", "MONOCHROME - ICS Silver", "2022/02/15", "RK480841705IN", "" ],
			// ["sinclairadair@gmail.com", "TRAVEL - FIP Ribbon", "2022/02/15", "RK480841609IN", "" ],
			// ["flomogu@hotmail.com", "MONOCHROME - ICS Gold|COLOR - YPS Ribbon", "2022/02/15", "RK480828695IN", "" ],
			// ["msbondar@mail.ru", "MONOCHROME - GPU Ribbon|COLOR - GPU Ribbon", "2022/02/15", "RK480841609IN", "" ],
			// ["uogogo.tw@yahoo.com.tw", "NATURE - GPU Ribbon", "2022/02/15", "RK480828704IN", "" ],
			// ["malabikaroy2@gmail.com", "TRAVEL - PSA Ribbon", "2022/02/15", "RK480828562IN", "" ],
			// ["yaseralaa@gmail.com", "TRAVEL - FIP Ribbon", "2022/02/15", "RK480841714IN", "" ],
			// ["sugiarto.widodo@gmail.com", "MONOCHROME - FIP Ribbon", "2022/02/15", "RK480841820IN", "" ],
			// ["ricardoqtrodrigues@gmail.com", "MONOCHROME - YPS Ribbon", "2022/02/15", "RK480841816IN", "" ],
			// ["mohammad.esteki.98@gmail.com", "TRAVEL - GPU Ribbon", "2022/02/15", "RK480841590IN", "" ],
			// ["bobgoode@blueyonder.co.uk", "MONOCHROME - MoL Gold Ribbon", "2022/02/15", "RK480841665IN", "" ],
			// ["d.wheeler@ucl.ac.uk", "COLOR - ICS Gold|COLOR - MoL Silver Ribbon|COLOR - ICS Best Participant - Color Section", "2022/02/15", "RK480841688IN", "" ],
			// ["graham@successnavigator.co.uk", "NATURE - MoL Gold Ribbon", "2022/02/15", "RK480841674IN", "" ],
			// ["squirrel.boyd1@gmail.com", "MONOCHROME - MoL Gold|MONOCHROME - YPS Ribbon", "2022/02/15", "RK480841691IN", "" ],
			// ["janez.podnar@siol.net", "COLOR - MoL Bronze Ribbon", "2022/02/15", "RK480841612IN", "" ],
			// ["eddyliphotographer@gmail.com", "TRAVEL - YPS Ribbon", "2022/02/15", "RK480841657IN", "" ],
			// ["noel.fenech@gmail.com", "MONOCHROME - FIAP Ribbon", "2022/02/15", "RK480841643IN", "" ],
			// ["schmidt.b.foto@gmx.de", "NATURE - YPS Ribbon|TRAVEL - YPS Ribbon", "2022/02/15", "RK480841630IN", "" ],
			// ["eainmatsaine@gmail.com", "TRAVEL - GPU Ribbon", "2022/02/15", "RK480841626IN", "" ],
			// ["rainboww17@hotmail.com", "MONOCHROME - PSA Ribbon", "2022/02/15", "RK480848681IN", "" ],
			// ["cowtech@earthlink.net", "MONOCHROME - MoL Silver Ribbon", "2022/02/15", "RK480841847IN", "" ],
			// ["tony_shi@yahoo.com", "MONOCHROME - GPU Ribbon|NATURE - FIP Ribbon", "2022/02/15", "RK480841731IN", "" ],
			// ["gcwatson@netspeed.com.au", "NATURE - YPS Ribbon", "2022/02/15", "RK480841745IN", "" ],
			// ["sureshvengarai@gmail.com", "NATURE - ICS Bronze", "2022/02/15", "RK480841878IN", "" ],
			// ["wsb@bigpond.net.au", "MONOCHROME - MoL Bronze Ribbon", "2022/02/15", "RK480841855IN", "" ],
			// ["johnjiang96@gmail.com", "MONOCHROME - YPS Ribbon", "2022/02/15", "RK480841759IN", "" ],
			// ["gracelee.123@gmail.com", "MONOCHROME - FIP Ribbon", "2022/02/15", "RK480841864IN", "" ],
			// ["levanvinhgl@gmail.com", "COLOR - YPS Ribbon", "2022/02/15", "RK480841776IN", "" ],
			// ["quochuyvapa@gmail.com", "NATURE - FIP Ribbon", "2022/02/15", "RK480841762IN", "" ],
			// ["nguyenhai1203@gmail.com", "MONOCHROME - FIP Ribbon", "2022/02/15", "RK480841780IN", "" ],
			// ["secmen7@hotmail.com", "MONOCHROME - FIP Ribbon|TRAVEL - FIP Ribbon", "2022/02/15", "RK480842357IN", "" ],
			// ["fedaicoskun@hotmail.com", "TRAVEL - MoL Bronze Ribbon", "2022/02/15", "RK480842343IN", "" ],
			// ["veysiarcagok@gmail.com", "COLOR - ICS Silver", "2022/02/15", "RK480842493IN", "" ],
			// ["stephen.mansup@gmail.com", "NATURE - FIP Ribbon", "2022/02/15", "RK480841881IN", "" ],
			// ["rob@kitesurfescapes.co.za", "NATURE - MoL Gold|NATURE - FIP Ribbon", "2022/02/15", "RK480841895IN", "" ],
			// ["percym45@gmail.com", "NATURE - MoL Silver|NATURE - FIP Ribbon", "2022/02/15", "RK480841904IN", "" ],
			// ["gillit@iafrica.com", "NATURE - MoL Bronze", "2022/02/15", "RK480841918IN", "" ],
			// ["janos.danis@digikabel.hu", "MONOCHROME - FIP Ribbon", "2022/02/15", "RK480842414IN", "" ],
			// ["tasko.barbara@gmail.com", "NATURE - PSA Ribbon", "2022/02/15", "RK480842405IN", "" ],
			// ["sebestyencirkos@gmail.com", "NATURE - PSA Ribbon", "2022/02/15", "RK480842502IN", "" ],
			// ["arina.sz@gmail.com", "MONOCHROME - FIP Ribbon", "2022/02/15", "RK480842391IN", "" ],
			// ["torok.tibor@gmail.com", "MONOCHROME - GPU Ribbon", "2022/02/15", "RK480842516IN", "" ],
			// ["sohelparvez1410@gmail.com", "TRAVEL - PSA Ribbon", "2022/02/15", "RK480842326IN", "" ],
			// ["rusho127@gmail.com", "NATURE - FIP Ribbon", "2022/02/15", "RK480842330IN", "" ],
			// ["sagorphotos.com@gmail.com", "TRAVEL - FIP Ribbon", "2022/02/15", "RK480842312IN", "" ],
			// ["madsenaage00@gmail.com", "COLOR - ICS Bronze", "2022/02/15", "RK480842520IN", "" ],
			// ["suszkiewicz@mail.dk", "MONOCHROME - FIAP Ribbon|COLOR - FIP Ribbon", "2022/02/15", "RK480842428IN", "" ],
			// ["isafoto@hotmail.com", "TRAVEL - FIAP Ribbon", "2022/02/15", "RK480842533IN", "" ],
			// ["faisal.khalaf555@gmail.com", "TRAVEL - FIP Ribbon", "2022/02/15", "RK480842431IN", "" ],
			// ["iupgjsy@163.com", "NATURE - FIP Ribbon", "2022/02/15", "RK480842547IN", "" ],
			// ["zhangzhimin@tqstar.cn", "NATURE - FIP Medal", "2022/02/15", "RK480842445IN", "" ],
			// ["minmaungmaungmyo2010@gmail.com", "COLOR - YPS Ribbon", "2022/02/15", "RK480842459IN", "" ],
			// ["honlanman@foxmail.com", "COLOR - YPS Ribbon", "2022/02/15", "RK480842462IN", "" ],
			// ["fotosopot@wp.pl", "TRAVEL - YPS Bronze|TRAVEL - YPS Ribbon|MONOCHROME - PSA Ribbon", "2022/02/15", "RK480842555IN", "" ],
			// ["liuqingshun@tqstar.cn", "NATURE - PSA Gold|COLOR - FIAP Ribbon", "2022/02/15", "RK480842564IN", "" ],
			// ["peterxiao@live.com", "NATURE - FIAP Gold|TRAVEL - FIP Medal|MONOCHROME - PSA Silver", "2022/02/15", "RK480842578IN", "" ],
			// ["phoecharphoto@gmail.com", "MONOCHROME - PSA Bronze", "2022/02/15", "RK480842595IN", "" ],
			// ["skt@macau.ctm.net", "TRAVEL - YPS Silver", "2022/02/15", "RK480842604IN", "" ],
			// ["irwin.wendy@yahoo.co.uk", "COLOR - YPS Bronze", "2022/02/15", "RK480842618IN", "" ],
			// ["adtamo@gmail.com", "COLOR - YPS Gold", "2022/02/15", "RK480842621IN", "" ],
			// ["hlamoenaing11694@gmail.com", "TRAVEL - PSA Gold|COLOR - PSA Ribbon", "2022/02/15", "RK480842635IN", "" ],
			// ["MaryPears@aol.com", "NATURE - GPU Gold", "2022/02/15", "RK480842652IN", "" ],
			// ["bbrobinb@hotmail.com", "MONOCHROME - YPS Bronze", "2022/02/15", "RK480842666IN", "" ],
			// ["duongtuanvutv@gmail.com", "NATURE - PSA Bronze", "2022/02/15", "RK480842670IN", "" ],
			// ["yongxiongling@gmail.com", "MONOCHROME - FIAP Gold|MONOCHROME - MoL Silver|MONOCHROME - ICS Best Participant - Monochrome Section", "2022/02/15", "RK480842683IN", "" ],
			// ["lybaertdaniel@hotmail.com", "COLOR - PSA Silver", "2022/02/15", "RK480842697IN", "" ],
			// ["lian_wei@yahoo.com", "NATURE - YPS Gold|TRAVEL - MoL Silver|NATURE - MoL Silver Ribbon|COLOR - YPS Ribbon|NATURE - YPS Ribbon|NATURE - ICS Best Participant - Nature Section", "2022/02/15", "RK480842706IN", "" ],
			// ["spotfoto@gmail.com", "COLOR - PSA Gold", "2022/02/15", "RK480842710IN", "" ],
			// ["fotobwfoto@gmail.com", "MONOCHROME - YPS Silver", "2022/02/15", "RK480841745IN", "" ],

			// Awards Mailed on 18th Feb 2022
			// ["e", "a", "2022/02/15", "t", "" ],
			["seham.mohammed222@gmail.com", "TRAVEL - PSA Bronze|MoL Golden Badge", "2022/02/18", "RK480843057IN", "" ],
			// ["e", "a", "2022/02/15", "t", "" ],

    );
?>
