# HanTok AI LMS 학습 관리 플랫폼

## 프로젝트 개요

HanTok은 AI 기반 초개인화 한국어 학습 플랫폼으로, 발음·문화·생활 코칭을 통합한 한국어 라이프 코치입니다.

**슬로건**: 한국의 삶을 선물하다 🎁

### 핵심 기능

- **AI 음성·텍스트 분석**: SpeechPro API를 통한 실시간 발음 평가
- **K-콘텐츠 학습**: K-드라마, 한국 문화를 통합한 학습 경험
- **초개인화 학습**: 사용자의 수준과 관심에 맞춘 맞춤형 콘텐츠
- **기업·학교·가정 모두를 위한 LMS**: B2B/B2C 모델 지원

## 기술 스택

- **LMS 플랫폼**: Moodle 4.4 (Docker 기반)
- **데이터베이스**: MySQL (utf8mb4)
- **외부 API**: SpeechPro (발음 평가), 음성 합성 API
- **테마**: Academi (Moodle Bootstrap 기반)
- **외부 접근**: ngrok HTTPS 터널 (mz-lms.ngrok.app)

## 빠른 시작

### 필수 요구사항
- Docker & Docker Compose
- MySQL 8.0+
- ngrok (선택사항, 외부 접근 시)

### 설치 및 실행

```bash
# 1. Docker 컨테이너 시작
docker-compose up -d

# 2. Moodle 초기화 (처음 1회만)
docker-compose exec moodle php admin/cli/install.php

# 3. 캐시 초기화
docker-compose exec moodle php admin/cli/purge_caches.php

# 4. 브라우저에서 접속
http://localhost:8888
```

### ngrok HTTPS 터널 설정

```bash
# ngrok HTTPS 터널 시작 (mz-lms.ngrok.app)
./setup-ngrok.sh

# 또는 수동으로
ngrok http --domain=mz-lms.ngrok.app 8888
```

## 주요 파일 구조

```
moodle/
├── admin/cli/
│   ├── update_speechpro_page_inline.php    # SpeechPro 음성 평가 페이지
│   └── ...
├── public/local/speechpro/
│   └── classes/service.php                  # SpeechPro API 통합
├── theme/academi/                           # HanTok 브랜딩 테마
├── config.php                               # Moodle 설정
└── docker-compose.yml                       # Docker 구성
```

## 데이터베이스 설정

### Academi 테마 설정 (mdl_config_plugins)

HanTok 브랜딩 설정:
- **slide1caption**: "HanTok: 한국의 삶을 선물하다"
- **slide2caption**: "AI 초개인화 학습 경험"
- **slide3caption**: "기업·학교·가정 모두를 위한 LMS"
- **phoneno**: "02-6954-8700"
- **emailid**: "yh.kim@mediazen.co.kr"
- **address**: "서울 강서구 마곡중앙12로 31"

### MySQL utf8mb4 설정

한국어 문자 인코딩을 위해 utf8mb4 사용:
```bash
mysql -h 127.0.0.1 -u moodle -p --default-character-set=utf8mb4 moodle < dump.sql
```

## SpeechPro API 통합

### API 엔드포인트

- **GTP**: 음성 파형 처리
- **Model**: 음성 특성 추출
- **Score**: 발음 점수 계산

### API 키 형식

주의: API 키는 **공백을 포함**해야 합니다:
```php
'syll ltrs' => $syllables,      // NOT 'syll_ltrs'
'syll phns' => $phonemes,       // NOT 'syll_phns'
'wav usr' => $audioData,        // NOT 'wav_usr'
```

## 개발 가이드

### 음성 평가 페이지 수정

[admin/cli/update_speechpro_page_inline.php](admin/cli/update_speechpro_page_inline.php) 참고

주요 기능:
- 음성 녹음 및 재생
- 3초 카운트다운
- 실시간 발음 점수 표시
- scoreData 중첩 구조 처리

### 캐시 초기화

```bash
docker-compose exec moodle php admin/cli/purge_caches.php
```

## 배포

### ngrok 배포

```bash
./start-service.sh
./start-ngrok.sh
```

### systemd 서비스

```bash
# 시작
sudo systemctl start ngrok-moodle.service

# 상태 확인
sudo systemctl status ngrok-moodle.service
```

## 연락처

- **전화**: 02-6954-8700
- **이메일**: yh.kim@mediazen.co.kr
- **주소**: 서울 강서구 마곡중앙12로 31

## 라이선스

Moodle은 GNU General Public License v3.0 라이선스 하에 배포됩니다.

## 참고 링크

- [Moodle 공식 문서][1]
- [SpeechPro API 문서](./SPEECHPRO_API_Interface.md)
- [ngrok 설정 가이드](./NGROK_SETUP.md)

[1]: https://docs.moodle.org/
