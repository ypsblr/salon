<?php
    // Data for communicating tracking number to award winners and catalog orders

    $tracking_site = "https://www.indiapost.gov.in";
    $posts_name = "India Posts";
    $mail_format = "fiap_awards_mailed.htm";
    $mailing_data = array(
            // Awards Mailed and Tracking No sent to participants on 20th June 2021
            // fiap_awards_mailed.htm
            ["ursuday03@gmail.com", "NATURE DIGITAL - FIAP Ribbon", "2021/06/19", "EK098723751IN", "" ],
            ["umiprathi@gmail.com", "MONOCHROME OPEN DIGITAL - FIAP Ribbon", "2021/06/19", "EK098723748IN", "" ],
            ["lybaertdaniel@hotmail.com", "NATURE DIGITAL - FIAP Ribbon", "2021/06/19", "CK082401983IN", "" ],
            // ["guoxijiang@hotmail.com", "MONOCHROME OPEN DIGITAL - FIAP Gold", "", "", "" ],
            ["mrrinalksen27@gmail.com", "TRAVEL DIGITAL - FIAP Ribbon", "2021/06/19", "EK098723527IN", "" ],
            ["allistairg1964@gmail.com", "MONOCHROME OPEN DIGITAL - FIAP Ribbon", "2021/06/19", "CK082401970IN", "" ],
            ["ed.de.wilde@skynet.be", "COLOR OPEN DIGITAL - FIAP Gold", "2021/06/19", "CK082401966IN", "" ],
            ["dineshbab007@gmail.com", "NATURE DIGITAL - FIAP Gold", "2021/06/19", "EK098723495IN", "" ],
            // ["abdulrahiman.ek@gmail.com", "TRAVEL DIGITAL - FIAP Gold", "", "", "We will leave the medal with Satish to find an opportunity to send to Dubai." ],
            ["santu.prosad1982@gmail.com", "TRAVEL DIGITAL - FIAP Ribbon", "2021/06/19", "EK098723500IN", "" ],
            ["biswajit.mukerjee@gmail.com", "COLOR OPEN DIGITAL - FIAP Ribbon", "2021/06/19", "EK098723646IN", "" ],
            ["fionc95125@gmail.com", "COLOR OPEN DIGITAL - FIAP Ribbon|FIAP Blue Pin - Best Entrant Digital", "2021/06/19", "CK082401997IN", "" ]

            // Awards Update sent on 4th June 2021
            // fiap_awards_update.htm
            // ["ursuday03@gmail.com", "NATURE DIGITAL - FIAP Ribbon", "", "", "" ],
            // ["uumiprathi@gmail.com", "MONOCHROME OPEN DIGITAL - FIAP Ribbon", "", "", "" ],
            // ["lybaertdaniel@hotmail.com", "NATURE DIGITAL - FIAP Ribbon", "", "", "" ],
            // ["guoxijiang@hotmail.com", "MONOCHROME OPEN DIGITAL - FIAP Gold", "", "", "" ],
            // ["mrrinalksen27@gmail.com", "TRAVEL DIGITAL - FIAP Ribbon", "", "", "" ],
            // ["allistairg1964@gmail.com", "MONOCHROME OPEN DIGITAL - FIAP Ribbon", "", "", "" ],
            // ["ed.de.wilde@skynet.be", "COLOR OPEN DIGITAL - FIAP Gold", "", "", "" ],
            // ["dineshbab007@gmail.com", "NATURE DIGITAL - FIAP Gold", "", "", "" ],
            // ["abdulrahiman.ek@gmail.com", "TRAVEL DIGITAL - FIAP Gold", "", "", "We will leave the medal with Satish to find an opportunity to send to Dubai." ],
            // ["santu.prosad1982@gmail.com", "TRAVEL DIGITAL - FIAP Ribbon", "", "", "" ],
            // ["biswajit.mukerjee@gmail.com", "COLOR OPEN DIGITAL - FIAP Ribbon", "", "", "" ],
            // ["fionc95125@gmail.com", "COLOR OPEN DIGITAL - FIAP Ribbon|FIAP Blue Pin - Best Entrant Digital", "", "", "" ]

            // Awards + Catalogs sent on 14th March 2021 - mailing_info_email.htm
            // ["rubenkalexander@gmail.com", "TRAVEL DIGITAL - MoL Gold", "2021/03/14", "EK378514871IN", "Catalog ordered is mailed separately." ],
            // ["shankarmsmys@gmail.com", "NATURE DIGITAL - FIP Gold", "2021/03/14", "EK378515007IN", "" ],
            // ["shuvrai@gmail.com", "TRAVEL DIGITAL - YPS Ribbon", "2021/03/14", "CK104895333IN", "Catalog ordered is mailed separately." ],
            // ["bahadur.subash@gmail.com", "TRAVEL DIGITAL - FIP Gold", "2021/03/14", "CK104895245IN", "Catalog ordered is mailed separately." ],
            // ["aggangadhar.ga@gmail.com", "MONOCHROME OPEN DIGITAL - YPS Silver", "2021/03/14", "EK378514899IN", "" ],
            // ["harishnn@gmail.com", "NATURE DIGITAL - PSA Gold", "2021/03/14", "EK378515015IN", "Catalogs ordered are mailed separately." ],
            // ["sudhiphoku@gmail.com", "NATURE DIGITAL - GPU Ribbon", "2021/03/14", "CK104895293IN", "Catalog ordered is mailed separately." ],
            // ["das.kishore@gmail.com", "MONOCHROME OPEN DIGITAL - YPS Ribbon", "2021/03/14", "CK104895276IN", "" ],
            // ["pranab.sarkar59@gmail.com", "COLOR OPEN DIGITAL - GPU Gold", "2021/03/14", "CK104895280IN", "" ],
            // ["umiprathi@gmail.com", "MOL Gold Badge - Best Master of Light Standard", "2021/03/14", "EK378514995IN", "Catalog ordered is mailed separately. MONOCHROME OPEN DIGITAL - FIAP Ribbon will be mailed on receipt from FIAP." ],
            // ["gunther.riehle@suntory.com", "NATURE DIGITAL - MoL Gold|TRAVEL DIGITAL - YPS Gold|TRAVEL DIGITAL - GPU Ribbon", "2021/03/14", "CK082433748IN", "" ],
            // ["iecmurray@hotmail.com", "COLOR OPEN DIGITAL - PSA Gold", "2021/03/14", "CK082433527IN", "" ],
            // ["drcranganath@gmail.com", "NATURE DIGITAL - YPS Ribbon", "2021/03/14", "EK378514885IN", "Catalog ordered is mailed separately." ],
            // ["aniruddha.das1973@gmail.com", "MONOCHROME OPEN DIGITAL - PSA Silver|COLOR OPEN DIGITAL - GPU Ribbon|COLOR OPEN DIGITAL - YPS Ribbon", "2021/03/14", "CK104895302IN", "" ],
            // ["dr.shanbhagh@rediffmail.com", "NATURE DIGITAL - YPS Bronze", "2021/03/14", "EK378515112IN", "" ],
            // ["radiphysics@gmail.com", "TRAVEL DIGITAL - PSA Silver", "2021/03/14", "CK104895259IN", "" ],
            // ["tutai_in@yahoo.com", "NATURE DIGITAL - YPS Ribbon", "2021/03/14", "CK104895320IN", "" ],
            // ["ashokkumar1968@rediffmail.com", "NATURE DIGITAL - PSA Silver", "2021/03/14", "CK104895262IN", "" ],
            // ["marianplaino@yahoo.com", "MONOCHROME OPEN DIGITAL - MoL Gold", "2021/03/14", "CK082433751IN", "" ],
            // ["fonteijne@zeelandnet.nl", "MONOCHROME OPEN DIGITAL - YPS Bronze", "2021/03/14", "CK082433513IN", "" ],
            // ["lybaertdaniel@hotmail.com", "MONOCHROME OPEN DIGITAL - PSA Gold", "2021/03/14", "CK082433632IN", "NATURE DIGITAL - FIAP Ribbon will be mailed on receipt from FIAP" ],
            // ["suszkiewicz@mail.dk", "COLOR OPEN DIGITAL - YPS Silver", "2021/03/14", "CK065893608IN", "" ],
            // ["guoxijiang@hotmail.com", "MONOCHROME OPEN DIGITAL - PSA Bronze", "2021/03/14", "CK082433500IN", "MONOCHROME OPEN DIGITAL - FIAP Gold will be mailed on receipt from FIAP." ],
            // ["mrrinalksen27@gmail.com", "NATURE DIGITAL - GPU Ribbon", "2021/03/14", "CK104895316IN", "TRAVEL DIGITAL - FIAP Ribbon will be mailed on receipt from FIAP." ],
            // ["sleungsalon@gmail.com", "COLOR OPEN DIGITAL - FIP Gold|MONOCHROME OPEN DIGITAL - YPS Ribbon|COLOR OPEN DIGITAL - YPS Ribbon", "2021/03/14", "CK082433646IN", "" ],
            // ["jacqueline.meertens2@gmail.com", "MONOCHROME OPEN DIGITAL - GPU Gold", "2021/03/14", "CK082433765IN", "" ],
            // ["ed.de.wilde@skynet.be", "MONOCHROME OPEN DIGITAL - YPS Gold|COLOR OPEN DIGITAL - YPS Bronze", "2021/03/14", "CK028433544IN", "COLOR OPEN DIGITAL - FIAP Gold will be mailed on receipt from FIAP." ],
            // ["bupix@email.de", "TRAVEL DIGITAL - PSA Gold|COLOR OPEN DIGITAL - MoL Gold|TRAVEL DIGITAL - YPS Ribbon", "2021/03/14", "CK082433734IN", "" ],
            // ["memes@teletu.it", "TRAVEL DIGITAL - GPU Ribbon", "2021/03/14", "CK065893483IN", "" ],
            // ["veniero.rubboli@alice.it", "MONOCHROME OPEN DIGITAL - GPU Ribbon", "2021/03/14", "CK065893364IN", "" ],
            // ["mdtranphotos@yahoo.com", "NATURE DIGITAL - YPS Gold|TRAVEL DIGITAL - PSA Bronze|YPS International Salon 2020 Catalog", "2021/03/14", "CK065893571IN", "" ],
            // ["skt@macau.ctm.net", "TRAVEL DIGITAL - YPS Silver", "2021/03/14", "CK065893452IN", "" ],
            // ["kari950@wp.pl", "COLOR OPEN DIGITAL - GPU Ribbon", "2021/03/14", "CK082433495IN", "" ],
            // ["fionc95125@gmail.com", "COLOR OPEN DIGITAL - PSA Bronze|NATURE DIGITAL - PSA Bronze|MONOCHROME OPEN DIGITAL - GPU Ribbon|YPS International Salon 2020 Catalog", "2021/03/14", "CK065893466IN", "COLOR OPEN DIGITAL - FIAP Ribbon and FIAP Blue Pin - Best Entrant Digital will be mailed on receipt from FIAP." ],
            // ["r.carton@telenet.be", "COLOR OPEN DIGITAL - YPS Gold|YPS International Salon 2020 Catalog", "2021/03/14", "CK065893585IN", "" ],
            // ["tasko.barbara@gmail.com", "NATURE DIGITAL - YPS Silver|TRAVEL DIGITAL - YPS Bronze", "2021/03/14", "CK082433629IN", "" ],
            // ["squirrel.boyd1@gmail.com", "MONOCHROME OPEN DIGITAL - FIP Gold|COLOR OPEN DIGITAL - PSA Silver", "2021/04/19", "CK082433615IN", "" ],

            // Catalogs alone
            // ["markku.mansson@gmail.com", "YPS International Salon 2020 Catalog", "2021/03/14", "CK065893470IN", "" ]

    );
?>
