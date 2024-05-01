
const wkPosBarcode = {
    id: '',
    sku: '',
    title: '',
    qty: '',
    type:'',
    printBarcode: '',
    printPop: '',
    tbCloseBtn: '',
    barcodeForm: '',
    barcodeDemo: '',
    barcodeWidth: '',
    barcodeSettings: '',
    barcodeReflector: '',
    barcodeList:'',
    barcodeConfig: wkposObj.barcode_config,

    init: function(){
        this.printBarcode = document.querySelectorAll('.print-barcode');
        this.barcodeList = document.querySelectorAll('.wkwc-pos-barcode');
        this.barcodeForm = document.querySelector('.wc-pos-barcode-generate');
        this.barcodeDemo = document.querySelector('.wc-pos-barcode-print-demo');
        this.barcodeReflector = document.querySelector('.wkwc-pos-barcode-reflect');
        this.barcodeSettings = document.querySelectorAll('input[name=_pos_barcode_width], input[name=_pos_barcode_bg_color], select[name=_pos_barcode_display_title], select[name=_pos_barcode_text_position], input[name=_pos_barcode_text_size], input[name=_pos_barcode_height]');
        this.initEvents();
    },
    initEvents: function () {
        (!!this.printBarcode.length) ? this.printBarcode.forEach(pb => {
            pb.addEventListener('click', this.handlePrintBarcode.bind(this));
        }) : null;
        (!!this.barcodeForm) ? this.barcodeForm.addEventListener('submit', this.handlePrintBarcodeRequest.bind(this)) : null;
        (!!this.barcodeSettings) ? this.barcodeSettings.forEach(setting => { setting.addEventListener('change', this.handleBarcodeSetting.bind(this) ) }) : null;

        (!!this.barcodeList) ? this.barcodeList.forEach(barcode => {
            this.handleBracodeList(barcode);
        }) : null;

        (!!this.barcodeReflector) ? this.initReflectorBarcode() : null;
    },
    initReflectorBarcode: function () {
        this.sku = this.barcodeReflector.getAttribute('data-barcode-value');
        this.title = this.barcodeReflector.getAttribute('data-barcode-text');
        this.generateBarcode('.wkwc-pos-barcode-reflect > svg');
    },
    handleBracodeList: function (barcode) {
        let newConfig = {
                "_pos_barcode_width": 0.5,
                "_pos_barcode_height": 20,
                "_pos_barcode_text_size": 14
            }
        this.sku = barcode.getAttribute('data-barcode-value');
        this.title = barcode.getAttribute('data-barcode-text');
        this.type = 'sku';
        this.generateBarcode(barcode.childNodes[0], newConfig);
    },
    handleBarcodeSetting: function (e) {
        this.barcodeConfig[e.target.getAttribute('name')] = e.target.value;
        this.barcodeReflector.innerHTML = "<svg></svg>";
        this.generateBarcode('.wkwc-pos-barcode-reflect > svg');
    },
    handlePrintBarcode: function (e) {
        e.preventDefault();
        this.barcodeConfig = wkposObj.barcode_config;
        this.printPop = document.querySelector('#printBarcode');
        this.tbCloseBtn = document.querySelector('#TB_closeWindowButton');
        (!!this.tbCloseBtn) ? this.tbCloseBtn.click() : null;
        this.sku    = e.target.getAttribute('data-image-sku');
        this.id     = e.target.getAttribute('data-image-id');
        this.title = e.target.getAttribute('data-title');
        this.printPop.style.display = 'block';
        this.printPop.style.height = '100%';
    },
    handlePrintBarcodeRequest: function (e) {
        e.preventDefault();
        let formData = new FormData(e.target);
        for (var [key, value] of formData.entries()) {
            if (key === 'quantity') {
                this.qty = value;
            }
            if (key === 'type') {
                this.type = value;
            }
        }

        if (this.verifyBarcodeData()) {
            this.barcodeDemo.innerHTML = '';
            this.barcodeDemo.insertAdjacentHTML('beforeend', '<svg></svg>');
            this.generateBarcode('.wc-pos-barcode-print-demo > svg').then(res => {
                this.barcodeDemo.style.display = 'inline-block';
                this.barcodeWidth = this.barcodeDemo.offsetWidth;
                this.barcodeDemo.style.display = 'none';
                let printContent = this.preparePrintContent();
                this.openPrintWindow(printContent);
            });
        }
    },
    verifyBarcodeData: function () {
        let status = true, msg = '';
        switch (true) {
            case (this.qty > 10000):
                msg = "Please enter value less than 10000";
                status = false;
                break;
            case (this.qty === '' || isNaN(this.qty)):
                msg = "Please enter value Integer value";
                status = false;
                break;
            case (this.type === 'sku' && this.sku === ''):
                msg = "Barcode by SKU is not created";
                status = false;
                break;
            case (this.type == 'id' && this.id == ''):
                msg = "Barcode by ID is not created";
                status = false;
                break;
            default:
                break;
        }
        (!status) ? alert(msg) : null
        return status;
    },
    preparePrintContent: function () {
            let printContents = `<div class="grid-template">`;
                for (var i = 0; i < this.qty; i++) {
                    printContents += `<div style="text-align:center"><span class="wkwc-pos-barcode"><svg></svg></span></div>`;
                }
            printContents += `</div>`;
        return printContents;
    },
    openPrintWindow: function (printContents) {
        var style_rules = [];
                if (wkposObj.page_preview === "landscape") {
                    style_rules.push(" @page { size: A4 Landscape;margin: 5mm;  } ");
                } else {
                    style_rules.push(" @page { size: A4;margin: 5mm;  } ");
                }
                var style = '<style type="text/css">' + style_rules.join("\n") + " .grid-template { display:grid;grid-template-columns: repeat(auto-fill, "+(( this.barcodeWidth !== 0) ? this.barcodeWidth +'px':'300px')+");justify-content: center;grid-gap:10px;}.wkwc-pos-barcode{display:block;} </style>";
                var printWindow = window.open("", "PRINT", "height=600,width=800");
                printWindow.document.write("<html><head><title></title>" + style);
                printWindow.document.write("</head><body>");
                printWindow.document.write(printContents);
                printWindow.document.write("</body></html>");
                printWindow.document.close(); // necessary for IE >= 10
                var printBarcodes = printWindow.document.querySelectorAll('.wkwc-pos-barcode');
                if (printBarcodes.length > 0) {
                    printBarcodes.forEach((barcode) => this.generateBarcode(barcode.childNodes[0]));
                }
                setTimeout(() => {
                    printWindow.focus(); // necessary for IE >= 10*-/
                    printWindow.print();
                    printWindow.close();
                }, 600);
    },
    generateBarcode: function (element, config = null) {
        return new Promise(resolve => {
            let generated =  JsBarcode(element, ((this.type === 'id') ? this.id : this.sku), {
                                textAlign: "center",
                                textPosition:  ( this.barcodeConfig._pos_barcode_display_title === 'disable' ) ? 'bottom': this.barcodeConfig._pos_barcode_text_position,
                                fontSize: (!!config) ? config._pos_barcode_text_size: this.barcodeConfig._pos_barcode_text_size,
                                background: this.barcodeConfig._pos_barcode_bg_color,
                                displayValue: ( this.barcodeConfig._pos_barcode_display_title === 'disable' ) ? false : true,
                                marginTop:5,
                                marginBottom:5,
                                marginLeft:15,
                                marginRight:15,
                                textMargin: 2,
                                height: parseInt((!!config) ? config._pos_barcode_height: this.barcodeConfig._pos_barcode_height),
                                width: parseFloat((!!config) ? config._pos_barcode_width: this.barcodeConfig._pos_barcode_width),
                                text: this.title,
            });
            resolve(generated);
        });
    }
}



document.addEventListener("DOMContentLoaded", () => {
    wkPosBarcode.init();
});

