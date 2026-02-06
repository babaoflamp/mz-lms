# Moodle ngrok HTTPS 설정 가이드

## 📋 설정 완료 요약

Moodle 사이트가 ngrok을 통해 **외부에서 HTTPS로 접근**할 수 있도록 설정되었습니다.

---

## 🔗 현재 공개 URL

```
https://brainlessly-unequestrian-ember.ngrok-free.dev
```

**주의**: ngrok URL은 재시작할 때마다 변경될 수 있습니다.

---

## 🛠️ 사용 방법

### 1️⃣ ngrok 시작
```bash
cd /home/scottk/Projects/moodle
./manage-ngrok.sh start
```

### 2️⃣ 현재 ngrok URL 확인
```bash
./manage-ngrok.sh url
```

### 3️⃣ ngrok 다시 시작
```bash
./manage-ngrok.sh restart
```

### 4️⃣ ngrok 중지
```bash
./manage-ngrok.sh stop
```

### 5️⃣ ngrok 로그 확인
```bash
./manage-ngrok.sh logs
```

---

## 📝 수정된 파일

### 1. `config.php` (수정)
- **변경**: ngrok HTTPS URL 설정 추가
- **설정 방식**: HTTP_HOST에서 ngrok 도메인 감지 시 HTTPS URL 사용

### 2. `manage-ngrok.sh` (신규)
- **기능**: ngrok 자동 관리 스크립트
- **명령어**: start, stop, restart, url, logs

### 3. `setup-ngrok.sh` (신규)
- **기능**: 초기 ngrok 설정 스크립트

### 4. `ngrok-moodle.service` (신규)
- **기능**: systemd 서비스 파일 (선택사항)
- **사용법**: `sudo systemctl start ngrok-moodle`

---

## 🔄 URL 변경 시 처리

ngrok 무료 플랜의 경우 URL이 재시작할 때마다 변경됩니다.

새로운 URL로 config.php를 자동 업데이트하려면:

```bash
./manage-ngrok.sh restart
```

스크립트가 자동으로:
1. ngrok 시작
2. 새로운 URL 추출
3. config.php 업데이트
4. 백업 파일 생성

---

## 🔐 보안 고려사항

### ngrok 사용 시 주의점:
1. **공개 URL**: 누구나 이 URL을 통해 접근 가능합니다
2. **기본 인증**: Moodle의 사용자 인증을 반드시 설정하세요
3. **방화벽**: 필요시 ngrok 대시보드에서 IP 제한을 설정할 수 있습니다

### 권장 설정:
- 정기적인 비밀번호 변경
- 강력한 비밀번호 정책 적용
- 정기적인 로그 모니터링

---

## 🎯 다음 단계

1. 브라우저에서 ngrok URL로 접속 확인
2. 외부 네트워크에서 접근 테스트
3. 필요시 ngrok Pro 플랜으로 업그레이드 (고정 URL 사용)
4. SSL 인증서 확인 (ngrok에서 자동 관리)

---

## 📊 ngrok 대시보드

모니터링 및 통계를 보려면:
```
http://localhost:4040
```

---

## ❌ 문제 해결

### ngrok이 시작되지 않음
```bash
# 기존 프로세스 종료
pkill -f "ngrok http"

# 로그 확인
./manage-ngrok.sh logs
```

### Moodle에 접근 불가
```bash
# 1. URL 확인
./manage-ngrok.sh url

# 2. config.php에서 wwwroot 확인
grep -A 5 "NGROK_URL" config.php

# 3. 캐시 초기화 (필요시)
sudo rm -rf moodledata/cache/* 2>/dev/null
```

### ngrok API 응답 없음
```bash
# 1. 잠시 기다렸다 재시도
sleep 3
./manage-ngrok.sh url

# 2. 재시작
./manage-ngrok.sh restart
```

---

## 📞 지원

ngrok 관련 문제: https://ngrok.com/support
Moodle 설정: https://docs.moodle.org/

---

## ✅ 설정 확인 체크리스트

- [x] ngrok 설치 확인
- [x] ngrok 인증 토큰 설정
- [x] Moodle config.php 수정
- [x] 관리 스크립트 생성
- [x] HTTPS URL 설정
- [x] 로컬 접근 호환성 유지

**모든 설정이 완료되었습니다! 🎉**
