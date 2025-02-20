// // Function to load Base64 font and register in pdfMake VFS
// function loadFont(callback) {
//   const basePath = window.location.origin + "/drupal/myschool/web/themes/custom/sis_pro/fonts/noto-sans-kr.txt";

//   fetch(basePath)
//     .then(response => response.text())
//     .then(base64 => {
//       // Trim spaces & newlines to avoid errors
//       base64 = base64.trim();

//       // Ensure the Base64 string is valid
//       if (!base64.startsWith("AAEAAAA")) {
//         console.error("❌ Invalid Base64 font data!");
//         return;
//       }

//       // ✅ Register the font in pdfMake's Virtual File System
//       pdfMake.vfs["NotoSansKR.ttf"] = base64;

//       // Register the font inside pdfMake
//       pdfMake.fonts = {
//         NotoSansKR: {
//           normal: "NotoSansKR.ttf", // ✅ Uses VFS instead of Base64
//           bold: "NotoSansKR.ttf",
//           italics: "NotoSansKR.ttf",
//           bolditalics: "NotoSansKR.ttf"
//         }
//       };

//       console.log("✅ NotoSansKR font registered in pdfMake VFS!");

//       if (callback) callback();
//     })
//     .catch(error => console.error("❌ Error loading font:", error));
// }

// // Load the font before initializing DataTables
// loadFont();
