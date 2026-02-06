#!/bin/bash

# H5P 예제 파일 다운로드 스크립트
# 기본 라이브러리를 포함한 간단한 H5P 콘텐츠들

echo "=== H5P 예제 콘텐츠 다운로드 ==="
echo ""

cd /home/scottk/Projects/moodle/h5p/

echo "📦 1. Course Presentation (슬라이드) 다운로드..."
curl -L -o course-presentation-example.h5p "https://h5p.org/h5p/embed/88.h5p"

echo "📦 2. Quiz (퀴즈) 다운로드..."
curl -L -o quiz-example.h5p "https://h5p.org/h5p/embed/97.h5p"

echo "📦 3. Drag and Drop (드래그앤드롭) 다운로드..."
curl -L -o drag-drop-example.h5p "https://h5p.org/h5p/embed/174.h5p"

echo "📦 4. Fill in the Blanks (빈칸 채우기) 다운로드..."
curl -L -o fill-blanks-example.h5p "https://h5p.org/h5p/embed/72.h5p"

echo "📦 5. Memory Game (기억력 게임) 다운로드..."
curl -L -o memory-game-example.h5p "https://h5p.org/h5p/embed/74.h5p"

echo ""
echo "✅ 다운로드 완료!"
echo ""
ls -lh *.h5p
echo ""
echo "이제 Moodle 강좌에서 이 파일들을 하나씩 H5P 활동으로 추가하세요!"
