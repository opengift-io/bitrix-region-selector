(function() {

	JCSeoScaner = function()
	{
		this.actionUrl = '/bitrix/admin/mango_seo_scaner.php?lang=' + BX.message('LANGUAGE_ID');
		this.started = false;

		BX.ready(BX.delegate(this.onScanComplete, this));
	};

	JCSeoScaner.prototype.isStarted = function() {
		return this.started;
	};

	JCSeoScaner.prototype.initializeScaning = function() {
		this.results = [];
		this.started = true;
		this.setProgress(0);
	};

	JCSeoScaner.prototype.onScanStart = function() {
		BX.show(BX('status_bar'));
		BX.hide(BX('start_button'));
		BX.hide(BX('first_start'));
        BX.showWait();
	};

	JCSeoScaner.prototype.onScanComplete = function() {
		BX.show(BX('start_container'));
		BX.show(BX('start_button'));
		BX.show(BX('first_start'));
		BX.hide(BX('status_bar'));
        BX.closeWait();
	};

	JCSeoScaner.prototype.setProgress = function(pProgress) {
		BX('progress_text').innerHTML = pProgress + '%';
		BX('progress_bar_inner').style.width = 500 * pProgress / 100 + 'px';
	};

	JCSeoScaner.prototype.sendScanRequest = function(pAction, pData, pSuccessCallback, pFailureCallback) {
		var action = pAction || 'scan';
		var data = pData || {};
		var successCallback = pSuccessCallback || BX.delegate(this.processScaningResults, this);
		var failureCallback = pFailureCallback || function(){BX.closeWait();alert(BX.message('MANGO_SEO_FINISH_ERROR_WAIT'));};
		data['interval'] = BX('scan-interval').value;
		data['limit'] = BX('scan-limit').value;
        data['site'] = BX('scan-site').value;
		data['action'] = action;
		data['sessid'] = BX.bitrix_sessid();
		data = BX.ajax.prepareData(data);

		return BX.ajax({
			timeout: 120,
			method: 'POST',
			dataType: 'json',
			url: this.actionUrl,
			data:  data,
			onsuccess: successCallback,
			onfailure: failureCallback
		});
	};

	JCSeoScaner.prototype.startStop = function() {
		if (this.isStarted()) {
			this.started = false;
			this.onScanComplete();
		} else {
			this.initializeScaning();
			this.sendScanRequest();
			this.onScanStart();
		}
	};

	JCSeoScaner.prototype.completeScaning = function() {
		this.onScanComplete();
		this.started = false;
	};

	JCSeoScaner.prototype.onRequestFailure = function(pReason) {
		this.onScanComplete();
		this.started = false;
	};

	JCSeoScaner.prototype.processScaningResults = function(pResponce) {
		if (!this.isStarted()) {
			return;
		}

		if (pResponce === 'ok' || pResponce === 'error') {
			return;
		}

		if (pResponce['all_done'] === 'Y') {
			BX('first_start').innerHTML = BX.message('MANGO_SEO_FINISH_SCAN');
			this.completeScaning();
		} else {
			this.sendScanRequest('scan', {lastID: pResponce['last']});
		}

		if (pResponce['percent']) {
			this.setProgress(pResponce['percent']);
		}
	};

})();