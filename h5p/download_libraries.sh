#!/bin/bash

# H5P 라이브러리 다운로드 스크립트

echo "=== H5P 라이브러리 다운로드 ==="
echo ""

# 다운로드 디렉토리 생성
mkdir -p h5p-libraries
cd h5p-libraries

echo "📦 Interactive Video 라이브러리 다운로드 중..."
curl -L -o H5P.InteractiveVideo-1.27.h5p "https://h5p.org/sites/default/files/h5p/exports/interactive-video-2-618.h5p"

echo "📦 Audio Recorder 라이브러리 다운로드 중..."
curl -L -o H5P.AudioRecorder-1.0.h5p "https://h5p.org/sites/default/files/h5p/exports/audio-recorder-142-1214919.h5p"

echo ""
echo "✅ 다운로드 완료!"
echo ""
echo "이제 Moodle에서:"
echo "1. 사이트 관리 → H5P → Manage H5P content types"
echo "2. 'Upload libraries' 또는 'Install library from file uploaded' 옵션"
echo "3. 다운로드한 .h5p 파일 업로드"
echo ""
echo "또는 강좌에서 직접 H5P 활동을 만들 때 이 파일들을 업로드하세요."
