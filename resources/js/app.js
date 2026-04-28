// ================================
// DataTables + Buttons (Bootstrap 5)
// ================================
import 'datatables.net-bs5';
import 'datatables.net-buttons-bs5';

// ================================
// Excel Export (JSZip)
// ================================
import JSZip from 'jszip';
window.JSZip = JSZip;

// ================================
// PDF Export (pdfmake)
// ================================
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';

pdfMake.vfs = pdfFonts.pdfMake.vfs;
window.pdfMake = pdfMake;

// ================================
// Buttons actions
// ================================
import 'datatables.net-buttons/js/buttons.html5';
import 'datatables.net-buttons/js/buttons.print';
