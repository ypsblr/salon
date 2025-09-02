<?php
// Systematic handling of honors entered by the participants to produce a meaningful honors list

// List of patterns for known honors and equivalent putputs


$list_of_honors = [
// RPS
array("orgn" => "RPS", "group" => "A", "pattern" => "FRPS", "distinction" => "FRPS"),
array("orgn" => "RPS", "group" => "A", "pattern" => "ARPS", "distinction" => "ARPS"),
array("orgn" => "RPS", "group" => "A", "pattern" => "LRPS", "distinction" => "LRPS"),

// PSA
array("orgn" => "PSA", "group" => "B", "pattern" => "Hon[-.: ]*FPSA", "distinction" => "HonFPSA"),
array("orgn" => "PSA", "group" => "B", "pattern" => "Hon[-.: ]*PSA", "distinction" => "HonPSA"),
array("orgn" => "PSA", "group" => "B", "pattern" => "FPSA", "distinction" => "FPSA"),
array("orgn" => "PSA", "group" => "B", "pattern" => "APSA", "distinction" => "APSA"),
array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA[-.: ]*pl", "distinction" => "GMPSA/P"),
array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA\/p", "distinction" => "GMPSA/P"),
array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA[-.: ]*go", "distinction" => "GMPSA/G"),
array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA\/g", "distinction" => "GMPSA/G"),
array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA[-.: ]*si", "distinction" => "GMPSA/S"),
array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA\/s", "distinction" => "GMPSA/S"),
array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA[-.: ]*br", "distinction" => "GMPSA/B"),
array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA\/b", "distinction" => "GMPSA/B"),
array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA", "distinction" => "GMPSA"),
array("orgn" => "PSA", "group" => "A", "pattern" => "MPSA2", "distinction" => "MPSA2"),
array("orgn" => "PSA", "group" => "A", "pattern" => "MPSA", "distinction" => "MPSA"),
array("orgn" => "PSA", "group" => "A", "pattern" => "EPSA", "distinction" => "EPSA"),
array("orgn" => "PSA", "group" => "A", "pattern" => "PPSA", "distinction" => "PPSA"),
array("orgn" => "PSA", "group" => "A", "pattern" => "QPSA", "distinction" => "QPSA"),

// FIAP
array("orgn" => "FIAP", "group" => "C", "pattern" => "Hon[-. ]*EFIAP", "distinction" => "HonEFIAP"),
array("orgn" => "FIAP", "group" => "B", "pattern" => "ES[-. ]*FIAP", "distinction" => "ESFIAP"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "MFIAP", "distinction" => "MFIAP"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*di[^, ]*3", "distinction" => "EFIAP/d3"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*d3", "distinction" => "EFIAP/d3"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*di[^, ]*2", "distinction" => "EFIAP/d2"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*d2", "distinction" => "EFIAP/d2"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*di[^, ]*1", "distinction" => "EFIAP/d1"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*d1", "distinction" => "EFIAP/d1"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*d[^123]", "distinction" => "EFIAP/d"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*pl", "distinction" => "EFIAP/p"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*p", "distinction" => "EFIAP/p"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*go", "distinction" => "EFIAP/g"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*g", "distinction" => "EFIAP/g"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*si", "distinction" => "EFIAP/s"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*s", "distinction" => "EFIAP/s"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*br", "distinction" => "EFIAP/b"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*b", "distinction" => "EFIAP/b"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP", "distinction" => "EFIAP"),
array("orgn" => "FIAP", "group" => "A", "pattern" => "AFIAP", "distinction" => "AFIAP"),

// FIP
array("orgn" => "FIP", "group" => "B", "pattern" => "ESEFIP", "distinction" => "ESEFIP"),
array("orgn" => "FIP", "group" => "B", "pattern" => "Hon[-. ]*FIP", "distinction" => "Hon.FIP"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*pl[-.:\/ ]*n", "distinction" => "EFIP/p(Nature)"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/p[-.:\/ ]*n", "distinction" => "EFIP/p(Nature)"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*go[-.:\/ ]*n", "distinction" => "EFIP/g(Nature)"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/g[-.:\/ ]*n", "distinction" => "EFIP/g(Nature)"),
array("orgn" => "FIP", "group" => "A", "pattern" => "MFIP[-.:\/ ]*n", "distinction" => "MFIP Nature"),
array("orgn" => "FIP", "group" => "A", "pattern" => "MFIP", "distinction" => "MFIP"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*pl[^n]*[,; ]", "distinction" => "EFIP/p"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/p", "distinction" => "EFIP/p"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*go[^n]*[,; ]", "distinction" => "EFIP/g"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/g", "distinction" => "EFIP/g"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*si", "distinction" => "EFIP/s"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/s", "distinction" => "EFIP/s"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*br", "distinction" => "EFIP/b"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/b", "distinction" => "EFIP/b"),
array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP", "distinction" => "EFIP"),
array("orgn" => "FIP", "group" => "A", "pattern" => "AFIP", "distinction" => "AFIP"),
array("orgn" => "FIP", "group" => "A", "pattern" => "FFIP", "distinction" => "FFIP"),

// ICS
array("orgn" => "ICS", "group" => "C", "pattern" => "HON[-.: ]*PIPICS", "distinction" => "HON.PIPICS"),
array("orgn" => "ICS", "group" => "B", "pattern" => "HON[-.: ]*MICS", "distinction" => "HON.MICS"),
array("orgn" => "ICS", "group" => "B", "pattern" => "HON[-.: ]*FICS", "distinction" => "HON.FICS"),
array("orgn" => "ICS", "group" => "A", "pattern" => "GWP[-.: ]*ICS", "distinction" => "GWP.ICS"),
array("orgn" => "ICS", "group" => "A", "pattern" => "ICS[-.: ]*SAFIIRI", "distinction" => "ICS.SAFIIRI"),
array("orgn" => "ICS", "group" => "A", "pattern" => "GM[-.: ]*ICS", "distinction" => "GM.ICS"),
array("orgn" => "ICS", "group" => "A", "pattern" => "BP[-.: ]*ICS", "distinction" => "BP.ICS"),
array("orgn" => "ICS", "group" => "A", "pattern" => "HON[-.: ]*EICS", "distinction" => "HON.EICS"),
array("orgn" => "ICS", "group" => "A", "pattern" => "MICS\/g", "distinction" => "MICS/g"),
array("orgn" => "ICS", "group" => "A", "pattern" => "MICS\/s", "distinction" => "MICS/s"),
array("orgn" => "ICS", "group" => "A", "pattern" => "MICS\/b", "distinction" => "MICS/b"),
array("orgn" => "ICS", "group" => "A", "pattern" => "MICS", "distinction" => "MICS"),
array("orgn" => "ICS", "group" => "A", "pattern" => "FICS", "distinction" => "FICS"),
array("orgn" => "ICS", "group" => "A", "pattern" => "AICS", "distinction" => "AICS"),

// GPU
array("orgn" => "GPU", "group" => "H", "pattern" => "GPU.*ze.*", "distinction" => "GPU.ZEUS"),
array("orgn" => "GPU", "group" => "G", "pattern" => "GPU.*her.*", "distinction" => "GPU.HERMES"),
array("orgn" => "GPU", "group" => "F", "pattern" => "GPU.*aph.*", "distinction" => "GPU.APHRODITE"),

array("orgn" => "GPU", "group" => "B", "pattern" => "GPU.*vip.*5", "distinction" => "GPU.VIP5"),
array("orgn" => "GPU", "group" => "B", "pattern" => "GPU.*vip.*4", "distinction" => "GPU.VIP4"),
array("orgn" => "GPU", "group" => "B", "pattern" => "GPU.*vip.*3", "distinction" => "GPU.VIP3"),
array("orgn" => "GPU", "group" => "B", "pattern" => "GPU.*vip.*2", "distinction" => "GPU.VIP2"),
array("orgn" => "GPU", "group" => "B", "pattern" => "GPU.*vip.*1", "distinction" => "GPU.VIP1"),
// array("orgn" => "GPU", "pattern" => "GPU.*vip[^12345]*", "distinction" => "GPU.VIP1"),

array("orgn" => "GPU", "group" => "A", "pattern" => "GPU.*cr.*5", "distinction" => "GPU.CR5"),
array("orgn" => "GPU", "group" => "A", "pattern" => "GPU.*cr.*4", "distinction" => "GPU.CR4"),
array("orgn" => "GPU", "group" => "A", "pattern" => "GPU.*cr.*3", "distinction" => "GPU.CR3"),
array("orgn" => "GPU", "group" => "A", "pattern" => "GPU.*cr.*2", "distinction" => "GPU.CR2"),
array("orgn" => "GPU", "group" => "A", "pattern" => "GPU.*cr.*1", "distinction" => "GPU.CR1"),
// array("orgn" => "GPU", "pattern" => "GPU.*cr[^12345]*", "distinction" => "GPU.CR1"),

// MOL
array("orgn" => "GPU", "group" => "B", "pattern" => "[^c*]MoL(\*){3}", "distinction" => "MoL***"),
array("orgn" => "GPU", "group" => "B", "pattern" => "[^c*]MoL(\*){2}", "distinction" => "MoL**"),
array("orgn" => "GPU", "group" => "B", "pattern" => "[^c*]MoL(\*){1}", "distinction" => "MoL*"),
array("orgn" => "GPU", "group" => "B", "pattern" => "[^c*]MoL", "distinction" => "MoL"),
array("orgn" => "GPU", "group" => "A", "pattern" => "c(\*){3}MoL", "distinction" => "c***MoL"),
array("orgn" => "GPU", "group" => "A", "pattern" => "c(\*){2}MoL", "distinction" => "c**MoL"),
array("orgn" => "GPU", "group" => "A", "pattern" => "c(\*){1}MoL", "distinction" => "c*MoL"),
array("orgn" => "GPU", "group" => "A", "pattern" => "cMoL", "distinction" => "cMoL"),

// IUP
array("orgn" => "IUP", "group" => "A", "pattern" => "GAIUP", "distinction" => "GAIUP"),
array("orgn" => "IUP", "group" => "A", "pattern" => "HIUP", "distinction" => "HIUP"),
array("orgn" => "IUP", "group" => "A", "pattern" => "MIUP", "distinction" => "MIUP"),
array("orgn" => "IUP", "group" => "A", "pattern" => "EIUP", "distinction" => "EIUP"),

];

$honors_exception_list = [];

function honors_text($honors, $profile_id = 0) {
    global $list_of_honors;

    if ($profile_id != 0 && isset($honors_exception_list[$profile_id]))
        return $honors_exception_list[$profile_id];

    $hlist = [];
    $cur_orgn = "";
    $cur_group = "";
    $match_found = false;
    foreach ($list_of_honors as $hon) {
        if ($hon["orgn"] == $cur_orgn && $hon["group"] == $cur_group && $match_found)
            continue;
        if ($hon["orgn"] != $cur_orgn || $hon["group"] != $cur_group) {
            $cur_orgn = $hon["orgn"];
            $cur_group = $hon["group"];
            $match_found = false;
        }
        if (preg_match("/[,; ]*" . $hon["pattern"] . "[,; ]*/i", $honors)) {
            $hlist[] = $hon["distinction"];
            $match_found = true;
        }
    }
    return implode(", ", $hlist);
}

// Test Code
// $list = ["EPSA, EFIAP, EFIP GOLD/N, GPU CR-3, GPU HERMES, A.APS, HON.CPE",
//             "AFIAP,EFIAP,HON.PESGSPC, HON.CPE, IIG/S",
//             "AFIAP, QPSA, GPU CR 3, GPU VIP2, GPU HERMES",
//             "AFIAP, FFIP, G.APS, GPA.PESGSPC,HON.PESGSPC",
//             "EFIAP/B, MPSA, C***MOL, EIUP, GPU CR-4, EFIP/G, EFIP/G (NATURE), AAPS, A.CPE, GPA. PESGSPC, HON. PESGSPC, G.APS",
//             "GMPSA, EFIAP/S; GPU VIP-5 CR-4 ZEUS, RISF-10, CMOL, GAPU",
//             "EFIAP, MPSA, GPU CR4, GPU VIP3, HIUP, A.APS, HON.CPE, A.CPE, GEPSS",
//             "MFIAP, EFIAP/P, EPSA, GPU CR4, GPU VIP4, HON.FWPG, GPU ZEUS",
//             "GMPSA, GMUPHK, GMVPSA, EFIAP/G, GPU CR4, MSAP",
//             "AFIAP, PPSA, GPU CR2, GPU VIP2, GPU APHRODITE",
//             "EFIAP/D1, FRPS, MPSA, MICS, MIUP, APSA, GWP.ICS, HON.YPS",
//             "MPSA,APSA,EFIAP, ARPS, FFIP, C*MOL, GAPU, HON.WPAI, HON.PESGSPC, GPA,PESGSPC, G.APS",
//             "QPSA, EFIP,, AIIPC , AICS, ASOF, A.APS, HON.FGNG, HON.APF",
//             "C***MOL - EFIAP/P - EPSA",
//             "EFIAP EPSA GPU CR3 VIP1 HERMES CMOL AICS AUSPA HON.FICS HON.PESGSPC HON.WPAI HON.CPE",
//             "EFIAP, EFIP, PPSA, GPU CROWN 3, CMOL, AAPG, HON.CPE, G.APS HON,PESGSPC,",
//             "GMPSA/P, EFIAP/P, FFIP, GPU CR5, SEPSS, GAIUP, VIP5, M.APS, E.CPE",
//             "GMPSA, GMSAP, GMGNG, SESAP, ESCPE, EFAIP, EFIP, HON.FPPS, HON.FCPA, HON.FSAP, HON.CPE, HON.WPG, HON.PIPC",
//             "EFIAP, EFIP, PPSA, GPU/CR3+VIP1+APHRODITE, C*MOL, SSS/B, BWPAI, ACPE, G.APS, HONMBAF, GPA.PESGSPC+HONPESGSPC, HONWPAI, HONFAPG, HONCPE, HONFGNG, HONFCPA, HONPIPC, HONAVTVISO",
//             "GMPSA/S, GPU-CR5, GPU-VIP4, EFIAP/B, HON.FWPAI",
//         ];
// foreach($list as $item) {
//     echo $item . "<br>";
//     echo "===> " . honors_text($item) . "<br><br>";
// }
?>
