<?php

// $Server = "localhost";
// $Username = "root";
// $Password = "";


// $DBName = "Omarty";



    $Server = "localhost";
    $Username = "kcgwebse_omartydbusr";
    $Password = "2IyNI*+f&N_Qb%wF";
    $DBName = "kcgwebse_omartyDB";

$conn = new mysqli($Server, $Username, $Password, $DBName);

 if($conn->error)
{
    die("there is some thing wrong  " . $conn->errno . " the error is " . $conn->error);
}


// add service categories.

$ServName = 
[
    "مصرف ابو ظبي الاسلامي",
    "البنك التجاري الدولي CIB",
    "بنك الاسكندرية",
    "بنك القاهرة",
    "بنك التعمير و الاسكان",
    "مركز ابن سينا",
    "مركز الاهرام الطبى",
    "اى ام سى المركز الطبى المصرى",
    "المركز الاسلامى الطبى",
    "مركز الامل لامراض الكلى",
    "صيدلية الهرم",
    "العزبي",
    "صيدلية الشروق",
    "صيدلية احمد ماهرة",
    "صيدلية مينا هاوس",
    "بيتزا كينج",
    "قصر الاهرام للمشويات - ابو شقرة",
    "لاسيرا",
    "الصياد للمأكولات البحرية",
    "دجاج تكا",
    "العمدة",
    "اسواق الصحابة",
    "اسواق الحرمين",
    "السويس",
    "اسواق العزيزية",
    "بنك اتش اس بى سى - HSBC",
    "كيو ان بى الاهلى - QNB",
    "بنك ايه بى سى - ABC",
    "البنك التجارى الدولى - CIB",
    "البنك الاهلى اليونانى",
    "صيدليات سيف",
    "صيدليات العزبى",
    "صيدلية د شريف الجنيدى",
    "فارماسى وان",
    "صيدلية د صفاء",
    "المركز الدولى للعلاج الطبيعى وعلاج الالام",
    "مركز القلب والاوعية الدموية",
    "مركز العاصمة الطبى",
    "مركز الشفاء الطبى",
    "المركز الطبى الدولى",
    "مطعم ابو غالى للمأكولات البحرية",
    "مطعم اللؤلؤة",
    "مطعم شيراز",
    "مطعم وكافيه كلاود ناين",
    "مطعم وكافيه باك يارد",
    "سوبر ماركت شيخون",
    "سعودى ماركت",
    "سوبر ماركت صن شاين",
    "سوبر ماركت جوهرة المدينة",
    "اسواق لبنان",
    "صيدليات كايرو ",
    "صيدليات 24",
    "صيدليات حلمى",
    "صيدلية رعاية",
    "صيدليات ابو على",
    "اكسبشن ماركت",
    "بيم ستورز",
    "سوبر ماركت اولاد البلد",
    "الحذيفى ماركت",
    "اسواق مكة",
    "مركز الاهرام للعلاج الطبيعى والتخسيس",
    "الفا للعيادات التخصصية",
    "مركز شفا الطبى",
    "مركز تبارك للاطفال",
    "برميم للعلاج الطبيعى والتأهيل الشامل للاطفال",
    "مطعم الشرقاوى",
    "مطعم وكافيه سبكترا",
    "اكسبشن العالمية ",
    "ستار فش",
    "ع طاسة",
    "مخبوزات أكلير",
    " الوردة الشامية ",
    "مخبز زغطوط",
    "مخبز الحرمين",
    "مخبز خير زمان",
    "صيدلية الزغبى",
    "صيدلية العاصمة",
    "صيدلية علبة",
    "صيدليات العزبى",
    "صيدلية البرلسى",
    "البنك التجارى الدولى - CIB",
    "بنك الكويت الوطنى",
    "البنك الاهلى المصرى",
    "بنك الاسكندرية",
    "المصرف العربى الدولى",
    "تيك اواى رابعة",
    "دجاج تكا",
    "توم اند بصل",
    "بعلبك",
    "الدوار",
    "مستشفى مدينة نصر",
    "مركز مصر لامراض الكلى",
    "مركز مصر لعلاج الاورام",
    "مركز د مجدى حسان",
    "ابداع النخبة للاستشارات النفسية",
    "اولاد رجب",
    "اسواق فاميلى",
    "اسواق المدينة",
    "الهنا فارم",
    "اسواق ام القرى",
    "صيدليات العزبى",
    "صيدليات سيف",
    "صيدلية د محمد فهمى مبارك",
    "صيدليات على وعلى",
    "صيدلية الشعب",
    "اسواق المدينة",
    "التوحيد",
    "الحرم الحسينى",
    "اسواق البخارى",
    "اسواق الحمد",
    "الدوار",
    "اسماك الحرية",
    "البرجولا",
    "حضرموت المعادى",
    "ستيك اوت",
    "البنك التجارى الدولى - CIB",
    "البنك العربي",
    "البنك الاهلى المصرى",
    "البنك العربى الافريقى الدولى",
    "بنك الشركة المصرفية العربية الدولية",
    "مستشفى السلام الدولى",
    "مركز القاهرة لامراض الكلى - سى كيه سى",
    "اى كير كلينيك - ا د معتز غيث",
    "بشائر الجنة",
    "المركز السويسرى الطبى بالقاهرة",
    "صيدليات ابو العز",
    "صيدلية د مها",
    "صيدلية نادى الجزيرة الرياضى",
    "صيدليات النادى",
    "صيدلية د عماد",
    "بليتش بريت",
    "مركز حياة",
    "مستشفى الصفوة - ا د سراج زكريا",
    "عيادات ام القرى",
    "المركز الدولى لطب الاسنان",
    "البنك التجارى الدولى",
    "البنك الاهلى المصرى",
    "بنك الكويت الوطنى",
    "بنك الاستثمار العربى",
    "البنك الاهلى المتحد",
    "اطلانتس ريستوران اند كافيه",
    "استاكوزا",
    "حاتى ابو حمزة",
    "دجاج تكا",
    "مطعم صهاريج عدن",
    "القدس",
    "تروفاليو",
    "اولاد رجب",
    "سبينيس",
    "اسواق العبد",
    "صيدلية ميريهام مدحت",
    "صييدليات رشدي",
    "صيدلية شاهين",
    "صيدلية د منى",
    "سيتروس",
    "توينكي",
    "اكسبشن ماركت",
    "سوق الرماية",
    "أسواق القدس",
    "مشويات إكسبشن",
    "التقوى ماركت",
];

$GovNameAR = 
[
    "الجيزة",
    "القاهرة",
    "الاسكندرية",
    "بورسعيد",
    "اسوان",
    "اسيوط",
    "الاسماعيلية",
    "الاقصر",
    "البحر الاحمر",
    "البحيرة",
    "الدقهلية",
    "السويس",
    "الشرقية",
    "الغربية",
    "الفيوم",
    "القليوبية",
    "المنوفية",
    "المنيا",
    "الوادي الجديد",
    "بني سويف",
    "جنوب سيناء",
    "دمياط",
    "سوهاج",
    "شمال سيناء",
    "قنا",
    "كفر الشيخ",
    "مرسى مطروح",

    ];

$GovNameEN = 
[
    "Giza",
    "Cairo",
    "Alexandria",
    "Port Said",
    "Aswan",
    "Asyut",
    "Ismailia",
    "Luxor",
    "The Red Sea",
    "Beheira",
    "Dakahlia",
    "Suez",
    "Sharqiya",
    "Gharbiya",
    "Fayoum",
    "Qalyubia",
    "Menoufia",
    "Minya",
    "New Valley",
    "Bani Sweif",
    "South of Sinaa",
    "Damietta",
    "Sohag",
    "North Sinai",
    "Qena",
    "Kafr El-Sheikh",
    "Marsa Matrouh",

    ];
    
    $ServPNI = 
    [
        "19951",
        "33878767",
        "19033",
        "37796492",
        "33899813",
        "37762433",
        "1159171988",
        "37802499",
        "33829115",
        "35873974",
        "33881105",
        "19600",
        "35825236",
        "19461",
        "233851847",
        "19519",
        "235844257",
        "35841150",
        "233888006",
        "19099",
        "235873394",
        "1152493359",
        "233827970",
        NULL,
        "235686995",
        "19007",
        "19700",
        "19123",
        "19666",
        "16272",
        "19199",
        "19600",
        "233442290",
        "19771",
        "233453336",
        "237611124",
        "233386916",
        "237617322",
        "237600837",
        "233059011",
        "233354885",
        "233358448",
        "233356908",
        "233468439",
        "233035676",
        "233036640",
        "16176",
        "233383317",
        "237613564",
        "233047154",
        "233905112",
        "19421",
        "16442",
        "233908694",
        "19141",
        "1063771888",
        "1113800062",
        "1111075781",
        "1001280557",
        "1143602273",
        "0233800741",
        "239743854",
        "233968912",
        "233800336",
        "233801774",
        "1126856911",
        "19491",
        "16687",
        "233742559",
        "1200781155",
        "1063203074",
        "1100888046",
        "1024697069",
        "1143796679",
        "1099353528",
        "223824250",
        "222743752",
        "222633683",
        "19600",
        "222622285",
        "19666",
        "19336",
        "19623",
        "19033",
        "19604",
        "222631123",
        "19099",
        "16405",
        "222641111",
        "16603",
        "222611402",
        "222753508",
        "222873379",
        "1009977072",
        "224035081",
        "19225",
        "222701800",
        "222600267",
        "223052197",
        "224732732",
        "19600",
        "19199",
        "225284428",
        "19905",
        "223586385",
        "225201405",
        "1229555975",
        "1005269203",
        "1066988393",
        "229706350",
        "16603",
        "225180166",
        "225283562",
        "224470999",
        "225195519",
        "19666",
        "19100",
        "225286031",
        "19555",
        "16668",
        "19885",
        "223598971",
        "225161313",
        "225183849",
        "225286895",
        "238522662",
        "238360662",
        "238303269",
        "16196",
        "238366263",
        "1286554914",
        "238382303",
        "16361",
        "238361973",
        "1224675554",
        "19666",
        "19623",
        "19336",
        "16697",
        "19072",
        "16649",
        "1009904401",
        "238326887",
        "19099",
        "238358922",
        "238377386",
        "238378326",
        "19225",
        "16005",
        "1061111292",
        "1006001777",
        "19011",
        "1112420006",
        "0101 267 3300",
        "233777070",
        "02 33844307",
        "02 33765528",
        "02 33776243",
        "1123858518",
        "233765528",
        "1011736930",
        ];
        
    $RegionNameAR = 
    [
        "وسط البلد",
    "مصر القديمة",
    "مصر الجديدة",
    "مدينة نصر",
    "مدينة السلام",
    "مدينة 15 مايو",
    "كوبري القبة",
    "غمرة",
    "عين شمس",
    "عين الصيرة",
    "عزبة النخل",
    "عابدين",
    "طرة",
    "شبرا",
    "روض الفرج",
    "دار السلام",
    "حلوان",
    "حلمية الزيتون",
    "حدائق القبة",
    "جسر السويس",
    "جاردن سيتي",
    "بولاق",
    "باب اللوق",
    "الهايكستب",
    "النزهة الجديدة",
    "الموسكي",
    "المنيل",
    "المقطم",
    "المعصرة",
    "المعادي",
    "المطرية",
    "المرج",
    "القاهرة الجديدة",
    "الفجالة",
    "العتبة",
    "العباسية",
    "الظاهر",
    "الشروق",
    "الشرابية",
    "السيدة زينب",
    "الساحل",
    "الزيتون",
    "الزمالك",
    "الزاوية الحمراء",
    "الدرب الاحمر",
    "الجمالية",
    "التجمع الخامس",
    "التبين",
    "البساتين",
    "الاميرية",
    "الازهر",
    "الازبكية",
    ];
    
    $RegionNameEN = 
    [
        "downtown",
        "Ancient Egypt",
        "Heliopolis",
        "Nasr City",
        "El-Salam",
        "May 15th City",
        "Dome bridge",
        "Ghamra",
        "Ain Shams",
        "Ain El-Sera",
        "Ezbet al-Nakhl",
        "Abdeen",
        "Tora",
        "Shubra",
        "Rod El-Farag",
        "Dar AISalaam",
        "Helwan",
        "Helmyat El-Zaytoon",
        "Dome Gardens",
        "Suez Bridge",
        "garden City",
        "Bulaq",
        "Bab Al-Luq",
        "hikstep",
        "New Nozha",
        "musky",
        "Manial",
        "Mokattam",
        "Maasara",
        "Maadi",
        "Matarya",
        "Marg",
        "New Cairo",
        "Faggala",
        "Attaba",
        "Abbasia",
        "Al-Dhaher",
        "Al-Shorouq",
        "El-Sharabyea",
        "El-Sayeda Zainab",
        "the coast",
        "El-Zaytoon",
        "Zamalek",
        "Red Corner",
        "El-Darb El-Ahmar",
        "El-Gamalya",
        "Fifth Settlement",
        "El-Tebbeen",
        "Basateen",
        "Amerya",
        "Azhar",
        "Azbakeya",

    ];
    
    $RegGovIds = 
    [
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        1,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        2,
        1 , 
    ];
    
    $CompoundNameAR = 
    [
        "رحاب أكتوبر سيتي",
        "أستوريا بارك",
        "بداية 1",
        "بداية 2",
        "الحي الايطالي",
        "الحي الاسباني",
    
    ];
    
    $CompoundNameEN = 
    [
        "Rehab October City",
        "Astoria Park",
        "Bedaya 1",
        "Bedaya 2",
        "Italian District",
        "Spanish District",

    ];
    
    $ServRegID = 
    [
        64,
        64,
        64,
        64,
        64,
        64,
        64,
        64,
        64,
        64,
        64,

    ];
    
    $ServCat = 
    [
        "صيدليات",
        "مطاعم",
        "سوبرماركت",
        "أطباء / مستشفيات",
        "سباك",
        "كهربائي",
        "ادوات صحية",
        "أثاث / منجد",
        "محل/سوق خضار",
        "حلاق / كوافير",
        "شركات اتصالات",
        "بنوك / صرافة",
        "كافيه / مقاهي",
        "نقاشة / ديكور",
        "مرافق وخدمات حكومية",
        "سواقين / نقل",
        "عمال مشال",
        "نظافة",
        "أمن / حراسة",
        "شرطة",
        "مواقف مواصلات",
        "مخبز",
    ];
    
    $ServCatIds = 
    [
        12,
        12,
        12,
        12,
        12,
        4,
        4,
        4,
        4,
        4,
        1,
        1,
        1,
        1,
        1,
        2,
        2,
        2,
        2,
        2,
        3,
        3,
        3,
        3,
        3,
        12,
        12,
        12,
        12,
        12,
        1,
        1,
        1,
        1,
        1,
        4,
        4,
        4,
        4,
        4,
        2,
        2,
        2,
        2,
        2,
        3,
        3,
        3,
        3,
        3,
        1,
        1,
        1,
        1,
        1,
        3,
        3,
        3,
        3,
        3,
        4,
        4,
        4,
        4,
        1,
        2,
        2,
        2,
        2,
        2,
        22,
        22,
        22,
        22,
        22,
        1,
        1,
        1,
        1,
        1,
        12,
        12,
        12,
        12,
        12,
        2,
        2,
        2,
        2,
        2,
        4,
        4,
        4,
        4,
        4,
        3,
        3,
        3,
        3,
        3,
        1,
        1,
        1,
        1,
        1,
        3,
        3,
        3,
        3,
        3,
        2,
        2,
        2,
        2,
        2,
        12,
        12,
        12,
        12,
        12,
        4,
        4,
        4,
        4,
        4,
        1,
        1,
        1,
        1,
        1,
        4,
        4,
        4,
        4,
        4,
        12,
        12,
        12,
        12,
        12,
        2,
        2,
        2,
        2,
        2,
        3,
        3,
        3,
        3,
        3,
        1,
        1,
        1,
        1,
        2,
        2,
        3,
        3,
        3,
        2,
        1 ,   
    ];
    
// foreach($ServName as $NameS)
// {
//     $sqlInsertServiceCat = $conn->query("INSERT INTO Service (Name) VALUES ('$NameS')");
// }

// foreach($GovNameAR as $NameS)
// {
//     $sqlInsertServiceCat = $conn->query("INSERT INTO Governate (GovName, CountryID) VALUES ('$NameS', 67)");
// }

// foreach($GovNameEN as $NameS)
// {
//         $sqlInsertServiceCat = $conn->query("UPDATE Governate SET GovNameEN = '$NameS' WHERE ID = '$i'");
// }

// foreach($ServCat as $NameS)
// {
//         $sqlInsertServiceCat = $conn->query("INSERT INTO ServiceCategory (Name_ar) VALUES('$NameS')");
//         $i++;
// }

// $i = 46;
// foreach($RegGovIds as $NameS)
// {
//         $sqlInsertServiceCat = $conn->query("UPDATE Region SET GovID = '$NameS' WHERE ID = '$i'");
//         $i++;
// }

// $i = 42;
// foreach($ServCatIds as $NameS)
// {
//         $sqlInsertServiceCat = $conn->query("UPDATE Service SET CountryID = '$NameS' WHERE ID = '$i'");
//         $i++;
// }


// $sqlGetServ = $conn->query("SELECT * FROM Service");
// $count = 0;
// $ID = 42;
// while($ServData = $sqlGetServ->fetch_row())
// {
//     $sqlInsertServiceCat = $conn->query("UPDATE Service SET PhoneNumI = '$ServPNI[$count]' WHERE ID = '$ID'");
//     $count++;
//     $ID++;
// }
    
    $sqlGetRegionGovID = $conn->query("SELECT ID, GovID FROM Region");
    while($RegGovID = $sqlGetRegionGovID->fetch_row());
    {
        $sqlUpdateServ = $conn->query("UPDATE Service SET GovernateID = '$RegGovID[1]' WHERE RegionID = '$RegGovID[0]'");
    }
    
    

?>