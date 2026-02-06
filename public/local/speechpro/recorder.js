(function () {
  "use strict";

  const textInput = document.getElementById("speechpro-text");
  const recordBtn = document.getElementById("speechpro-record");
  const stopBtn = document.getElementById("speechpro-stop");
  const evalBtn = document.getElementById("speechpro-evaluate");
  const statusEl = document.getElementById("speechpro-status");
  const resultEl = document.getElementById("speechpro-result");

  if (
    !textInput ||
    !recordBtn ||
    !stopBtn ||
    !evalBtn ||
    !statusEl ||
    !resultEl
  ) {
    return;
  }

  let mediaRecorder = null;
  let recordedChunks = [];
  let recordedBlob = null;

  const strings =
    (window.M && window.M.str && window.M.str.local_speechpro) || {};
  const setStatus = (key) => {
    statusEl.textContent = strings[key] || key;
  };

  const setError = (key, detail) => {
    statusEl.classList.remove("alert-info");
    statusEl.classList.add("alert-danger");
    statusEl.textContent =
      (strings[key] || key) + (detail ? ` (${detail})` : "");
  };

  const resetStatusStyle = () => {
    statusEl.classList.remove("alert-danger");
    statusEl.classList.add("alert-info");
  };

  const enableButtons = (recordEnabled, stopEnabled, evalEnabled) => {
    recordBtn.disabled = !recordEnabled;
    stopBtn.disabled = !stopEnabled;
    evalBtn.disabled = !evalEnabled;
  };

  const getSesskey = () =>
    (window.M && window.M.cfg && window.M.cfg.sesskey) || "";

  const createTestWav = (durationMs = 1000) => {
    const sampleRate = 16000;
    const channels = 1;
    const samples = (sampleRate * durationMs) / 1000;
    const sampleWidth = 2;
    const byteRate = sampleRate * channels * sampleWidth;
    const blockAlign = channels * sampleWidth;

    const buffer = new ArrayBuffer(44 + samples * sampleWidth);
    const view = new DataView(buffer);

    const writeString = (offset, str) => {
      for (let i = 0; i < str.length; i++) {
        view.setUint8(offset + i, str.charCodeAt(i));
      }
    };

    let offset = 0;
    writeString(offset, "RIFF");
    offset += 4;
    view.setUint32(offset, 36 + samples * sampleWidth, true);
    offset += 4;
    writeString(offset, "WAVE");
    offset += 4;
    writeString(offset, "fmt ");
    offset += 4;
    view.setUint32(offset, 16, true);
    offset += 4;
    view.setUint16(offset, 1, true);
    offset += 2;
    view.setUint16(offset, channels, true);
    offset += 2;
    view.setUint32(offset, sampleRate, true);
    offset += 4;
    view.setUint32(offset, byteRate, true);
    offset += 4;
    view.setUint16(offset, blockAlign, true);
    offset += 2;
    view.setUint16(offset, 16, true);
    offset += 2;
    writeString(offset, "data");
    offset += 4;
    view.setUint32(offset, samples * sampleWidth, true);
    offset += 4;

    // Generate white noise
    for (let i = 0; i < samples; i++) {
      const sample = (Math.random() - 0.5) * 0.3;
      view.setInt16(offset, sample * 0x7fff, true);
      offset += 2;
    }

    return new Blob([buffer], { type: "audio/wav" });
  };

  const createTestWavWithTone = (durationMs = 1000, frequency = 440) => {
    const sampleRate = 16000;
    const channels = 1;
    const samples = (sampleRate * durationMs) / 1000;
    const sampleWidth = 2;
    const byteRate = sampleRate * channels * sampleWidth;
    const blockAlign = channels * sampleWidth;

    const buffer = new ArrayBuffer(44 + samples * sampleWidth);
    const view = new DataView(buffer);

    const writeString = (offset, str) => {
      for (let i = 0; i < str.length; i++) {
        view.setUint8(offset + i, str.charCodeAt(i));
      }
    };

    let offset = 0;
    writeString(offset, "RIFF");
    offset += 4;
    view.setUint32(offset, 36 + samples * sampleWidth, true);
    offset += 4;
    writeString(offset, "WAVE");
    offset += 4;
    writeString(offset, "fmt ");
    offset += 4;
    view.setUint32(offset, 16, true);
    offset += 4;
    view.setUint16(offset, 1, true);
    offset += 2;
    view.setUint16(offset, channels, true);
    offset += 2;
    view.setUint32(offset, sampleRate, true);
    offset += 4;
    view.setUint32(offset, byteRate, true);
    offset += 4;
    view.setUint16(offset, blockAlign, true);
    offset += 2;
    view.setUint16(offset, 16, true);
    offset += 2;
    writeString(offset, "data");
    offset += 4;
    view.setUint32(offset, samples * sampleWidth, true);
    offset += 4;

    // Generate sine wave tone
    for (let i = 0; i < samples; i++) {
      const sample = Math.sin((2 * Math.PI * frequency * i) / sampleRate) * 0.3;
      view.setInt16(offset, sample * 0x7fff, true);
      offset += 2;
    }

    return new Blob([buffer], { type: "audio/wav" });
  };


    resetStatusStyle();
    resultEl.innerHTML = "";
    recordedBlob = null;
    recordedChunks = [];

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      setError("error_permission");
      return;
    }

    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      mediaRecorder = new MediaRecorder(stream);
      mediaRecorder.ondataavailable = (event) => {
        if (event.data && event.data.size > 0) {
          recordedChunks.push(event.data);
        }
      };
      mediaRecorder.onstop = () => {
        recordedBlob = new Blob(recordedChunks, { type: "audio/webm" });
        enableButtons(true, false, true);
        setStatus("status_ready");
      };
      mediaRecorder.start();
      enableButtons(false, true, false);
      setStatus("status_recording");
    } catch (err) {
      setError("error_recording", err && err.message ? err.message : "");
    }
  };

  const stopRecording = () => {
    if (mediaRecorder && mediaRecorder.state !== "inactive") {
      mediaRecorder.stop();
    }
  };

  const renderResult = (data) => {
    const score = data && data.score ? data.score : null;
    const scoreValue = typeof score === "number" ? score : score && score.score;
    const details =
      data && data.score
        ? data.score.details || data.score.result || data.score
        : null;

    let html = "";
    html += '<div class="card"><div class="card-body">';
    html += '<h5 class="card-title">SpeechPro 결과</h5>';
    if (scoreValue !== null && scoreValue !== undefined) {
      html += `<p><strong>Score:</strong> ${scoreValue}</p>`;
    }
    if (details) {
      html += `<pre class="small bg-light p-2">${JSON.stringify(details, null, 2)}</pre>`;
    }
    html += "</div></div>";
    resultEl.innerHTML = html;
  };

  const encodeWav = (audioBuffer) => {
    const numChannels = audioBuffer.numberOfChannels;
    const sampleRate = audioBuffer.sampleRate;
    const format = 1;
    const bitDepth = 16;
    const samples = audioBuffer.length;
    const blockAlign = (numChannels * bitDepth) / 8;
    const byteRate = sampleRate * blockAlign;
    const dataSize = samples * blockAlign;
    const buffer = new ArrayBuffer(44 + dataSize);
    const view = new DataView(buffer);

    const writeString = (offset, str) => {
      for (let i = 0; i < str.length; i++) {
        view.setUint8(offset + i, str.charCodeAt(i));
      }
    };

    let offset = 0;
    writeString(offset, "RIFF");
    offset += 4;
    view.setUint32(offset, 36 + dataSize, true);
    offset += 4;
    writeString(offset, "WAVE");
    offset += 4;
    writeString(offset, "fmt ");
    offset += 4;
    view.setUint32(offset, 16, true);
    offset += 4;
    view.setUint16(offset, format, true);
    offset += 2;
    view.setUint16(offset, numChannels, true);
    offset += 2;
    view.setUint32(offset, sampleRate, true);
    offset += 4;
    view.setUint32(offset, byteRate, true);
    offset += 4;
    view.setUint16(offset, blockAlign, true);
    offset += 2;
    view.setUint16(offset, bitDepth, true);
    offset += 2;
    writeString(offset, "data");
    offset += 4;
    view.setUint32(offset, dataSize, true);
    offset += 4;

    const channelData = [];
    for (let ch = 0; ch < numChannels; ch++) {
      channelData.push(audioBuffer.getChannelData(ch));
    }

    for (let i = 0; i < samples; i++) {
      for (let ch = 0; ch < numChannels; ch++) {
        let sample = channelData[ch][i];
        sample = Math.max(-1, Math.min(1, sample));
        view.setInt16(
          offset,
          sample < 0 ? sample * 0x8000 : sample * 0x7fff,
          true,
        );
        offset += 2;
      }
    }

    return new Blob([buffer], { type: "audio/wav" });
  };

  const convertToWav = async (blob) => {
    const arrayBuffer = await blob.arrayBuffer();
    const audioContext = new (
      window.AudioContext || window.webkitAudioContext
    )();
    const audioBuffer = await audioContext.decodeAudioData(arrayBuffer);
    return encodeWav(audioBuffer);
  };

  const evaluateRecording = async () => {
    resetStatusStyle();
    resultEl.innerHTML = "";

    const text = (textInput.value || "").trim();
    if (!text) {
      setError("error_generic", "텍스트를 입력하세요");
      return;
    }
    if (!recordedBlob) {
      setError("error_generic", "녹음 파일이 없습니다");
      return;
    }

    setStatus("status_processing");
    enableButtons(false, false, false);

    try {
      const wavBlob = await convertToWav(recordedBlob);
      const formData = new FormData();
      formData.append("text", text);
      formData.append("audio", wavBlob, "recording.wav");

      const response = await fetch(
        `/local/speechpro/ajax.php?action=evaluate&sesskey=${encodeURIComponent(getSesskey())}`,
        {
          method: "POST",
          body: formData,
        },
      );

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      if (data.error) {
        setError("error_generic", data.error);
      } else {
        renderResult(data);
        setStatus("status_done");
      }
    } catch (err) {
      setError("error_network", err && err.message ? err.message : "");
    } finally {
      enableButtons(true, false, !!recordedBlob);
    }
  };

  const useTestAudio = () => {
    resetStatusStyle();
    resultEl.innerHTML = "";
    
    // Create test WAV with tone (1 second)
    recordedBlob = createTestWavWithTone(1000, 440);
    enableButtons(true, false, true);
    setStatus("status_ready");
  };

  recordBtn.addEventListener("click", startRecording);
  stopBtn.addEventListener("click", stopRecording);
  evalBtn.addEventListener("click", evaluateRecording);

  // Add test button for headless environments
  const testBtn = document.getElementById("speechpro-test");
  if (testBtn) {
    testBtn.addEventListener("click", useTestAudio);
  }
})();

```
