function pdfDraw() {
  // If absolute URL from the remote server is provided, configure the CORS
  // header on that server.

  //var url = '//cdn.mozilla.net/pdfjs/tracemonkey.pdf';
  //var url = 'uploads/2018-03/2018-03-14-1.pdf';
  var url = './2018-03-14-1.pdf';


  // Loaded via <script> tag, create shortcut to access PDF.js exports.
  var pdfjsLib = window['pdfjs-dist/build/pdf'];

  // The workerSrc property shall be specified.
  //pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';
  pdfjsLib.GlobalWorkerOptions.workerSrc = 'wSBRZGk1XXQ3dj4G81YxKxPzvuW60/scripts/pdf.worker.js';

  var pdfDoc = null,
      pageNum = 1,
      pageRendering = false,
      pageNumPending = null,
      scale = 1.0,
      canvas = document.getElementById('the-canvas'),
      ctx = canvas.getContext('2d');

  /**
   * Get page info from document, resize canvas accordingly, and render page.
   * @param num Page number.
   */
  function renderPage(num, scaleInternal) {
    pageRendering = true;
    // Using promise to fetch the page
    pdfDoc.getPage(num).then(function(page) {
      var viewport = page.getViewport(scaleInternal);
      canvas.height = viewport.height;
      canvas.width = viewport.width;

      // Render PDF page into canvas context
      var renderContext = {
        canvasContext: ctx,
        viewport: viewport
      };
      var renderTask = page.render(renderContext);

      // Wait for rendering to finish
      renderTask.promise.then(function() {
        pageRendering = false;
        if (pageNumPending !== null) {
          // New page rendering is pending
          renderPage(pageNumPending, scale);
          pageNumPending = null;
        }
      });
    });

    // Update page counters
    document.getElementById('page_num').textContent = num;
  }

  /**
   * If another page rendering in progress, waits until the rendering is
   * finised. Otherwise, executes rendering immediately.
   */
  function queueRenderPage(num) {
    if (pageRendering) {
      pageNumPending = num;
    } else {
      renderPage(num, scale);
    }
  }

  /**
   * Displays previous page.
   */
  function onPrevPage() {
    if (pageNum <= 1) {
      return;
    }
    pageNum--;
    queueRenderPage(pageNum, scale);
  }
  document.getElementById('prev').addEventListener('click', onPrevPage);

  /**
   * Displays next page.
   */
  function onNextPage() {
    if (pageNum >= pdfDoc.numPages) {
      return;
    }
    pageNum++;
    queueRenderPage(pageNum, scale);
  }
  document.getElementById('next').addEventListener('click', onNextPage);



/**
   * scale = 0.5
   */
  function makeSmaller() {
    renderPage(pageNum, 0.5);
  }
  document.getElementById('small').addEventListener('click', makeSmaller);


/**
   * scale = 0.5
   */
  function makeBigger() {
    renderPage(pageNum, 2.0);
  }
  document.getElementById('big').addEventListener('click', makeBigger);

  


  /**
   * get another doc
   */  /*
  function getNextDoc() {
    doc = pdfjsLib.getDocument('test1.pdf');
    initialDraw();
  }
  document.getElementById('nextDoc').addEventListener('click', getNextDoc);


  var doc = pdfjsLib.getDocument(url);
  initialDraw();
  

  function initialDraw () {
    doc.then(function(pdfDoc_) {
      pdfDoc = pdfDoc_;
      document.getElementById('page_count').textContent = pdfDoc.numPages;

      // Initial/first page rendering
      renderPage(pageNum, scale);
    });
  } */


  /**
   * Asynchronously downloads PDF.
   */
  pdfjsLib.getDocument(url).then(function(pdfDoc_) {
    pdfDoc = pdfDoc_;
    document.getElementById('page_count').textContent = pdfDoc.numPages;

    // Initial/first page rendering
    renderPage(pageNum, scale);
  });


}