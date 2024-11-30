import './bootstrap';


const fileInput = document.querySelector('#fileInput');
const uploadUrl = "/upload";
const chunkSize = 1024 * 1024;
const maxRetries = 5;

let currentChunk = 0;
let totalChunks = 0;
let file = null;
let retryCount = 0;
let isUploadingProcess = false;
let successChunks = [];

const uploadChunk = () => {
    if (!file || currentChunk >= totalChunks || !navigator.onLine) {
        console.log("Завантаження завершено!");
        return;
    }
    if (successChunks.includes(currentChunk)) {
        currentChunk++;
        uploadChunk();
        return;
    }

    const start = currentChunk * chunkSize;
    const end = Math.min(file.size, start + chunkSize);
    const chunk = file.slice(start, end);
    const formData = new FormData();
    formData.append("file", chunk);
    formData.append("fileName", file.name);
    formData.append("chunk", currentChunk);
    formData.append("totalChunks", totalChunks);
    isUploadingProcess = true;

    axios
        .post(uploadUrl, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        })
        .then(() => {
            console.log(`Чанк ${currentChunk + 1}/${totalChunks} завантажено`);
            successChunks.push(currentChunk);
            currentChunk++;
            retryCount = 0;
            uploadChunk();
        })
        .catch((error) => {
            console.error(`Помилка завантаження чанка ${currentChunk + 1}: ${error.message}`);
            if (retryCount < maxRetries) {
                console.log(`Повторна спроба ${retryCount + 1} для чанка ${currentChunk + 1}`);
                retryCount++;
                setTimeout(uploadChunk, 3000);
            } else {
                console.error("Максимальна кількість спроб досягнута. Чекаємо відновлення мережі...");
                isUploadingProcess = false;
            }
        });
};

const startUpload = (selectedFile) => {
    file = selectedFile;
    totalChunks = Math.ceil(file.size / chunkSize);
    currentChunk = 0;
    retryCount = 0;

    uploadChunk();
};

fileInput.addEventListener("change", (event) => {
    const selectedFile = event.target.files[0];
    if (!selectedFile) return;

    if (!navigator.onLine) {
        alert("Інтернет-з'єднання відсутнє. Будь ласка, зачекайте відновлення.");
        return;
    }

    startUpload(selectedFile);
});

window.addEventListener("online", () => {
    currentChunk = 0;
    if (!isUploadingProcess && file && successChunks.length < totalChunks) {
        console.log("Інтернет відновлено. Продовжуємо завантаження...");
        uploadChunk();
    }
});

window.addEventListener("offline", () => {
    console.warn("Інтернет-з'єднання втрачено. Завантаження призупинено.");
});

