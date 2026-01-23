document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("registration-form");
  const alerts = document.getElementById("alert-placeholder");

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const data = Object.fromEntries(new FormData(form));

    try {
      Loading.clearAndShow(alerts, "Registering...");
      Loading.disableForm(form);

      const user = await Api.post(window.routes.createUser, {
        email: data.email,
        password: data.password,
      });

      const auth = await Api.post(window.routes.login, {
        email: data.email,
        password: data.password,
      });

      // Handle both token and accessToken response formats
      const accessToken = auth.token || auth.accessToken;
      const refreshToken = auth.refresh_token || auth.refreshToken;

      if (!accessToken) {
        throw new Error("Access token is missing.");
      }

      // Store access token
      Api.setToken(accessToken);

      // Store refresh token if present
      if (refreshToken) {
        Api.setRefreshToken(refreshToken);
      }

      await Api.post(
        window.routes.createProfile,
        { userId: user.id, name: data.name, bio: data.bio }
      );

      Loading.hide(alerts);
      Alert.append(alerts, "You have been successfully registered!", "success");
      window.location.href = window.routes.successRedirect;
    } catch (e) {
      Loading.hide(alerts);
      Alert.append(alerts, "Registration failed", "danger");
    } finally {
      Loading.enableForm(form);
    }
  });
});
